<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\WaterRecord;
use App\Models\User;
use Carbon\Carbon;

class WaterRecordController extends Controller
{
    /**
     * Store or update a daily/date-by-date water record.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nic' => 'required|integer|exists:users,nic',
            'date' => 'required|date_format:Y-m-d',
            'water_rate' => 'nullable|numeric|min:0',
            'points' => 'required|numeric|min:0',
            'liters' => 'nullable|numeric|min:0',
            'bill' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $nic = $request->nic;
            $date = $request->date;
            $waterRate = $request->filled('water_rate') && (float)$request->water_rate > 0 ? (float)$request->water_rate : 50.0;
            $points = (float)$request->points; // Points is Units
            
            // If liters is sent from ESP, use it; otherwise calculate from points (1 unit = 1000L)
            $liters = $request->filled('liters') ? (float)$request->liters : round($points * 1000.0, 2);

            // Calculate bill based on units: bill = points * water_rate
            $bill = ($request->has('bill') && $request->bill !== null && $request->bill !== '') 
                ? (float)$request->bill 
                : round($points * $waterRate, 2);

            // Create or update the record for the specific user and date
            $record = WaterRecord::updateOrCreate(
                [
                    'nic' => $nic,
                    'date' => $date
                ],
                [
                    'water_rate' => $waterRate,
                    'points' => $points,
                    'liters' => $liters,
                    'bill' => $bill
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Water record saved successfully',
                'data' => $record
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save water record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retrieve all water records for a given user (nic).
     */
    public function index($nic)
    {
        try {
            $userExists = User::where('nic', $nic)->exists();
            if (!$userExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'User with this NIC does not exist'
                ], 404);
            }

            $records = WaterRecord::where('nic', $nic)
                ->orderBy('date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'count' => $records->count(),
                'data' => $records
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve water records',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retrieve a specific daily water record for a user by date.
     */
    public function showByDate($nic, $date)
    {
        try {
            $validator = Validator::make(['date' => $date], [
                'date' => 'required|date_format:Y-m-d'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date format. Expected Y-m-d.',
                    'errors' => $validator->errors()
                ], 400);
            }

            $record = WaterRecord::where('nic', $nic)
                ->where('date', $date)
                ->first();

            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => 'No water record found for the specified user and date'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $record
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve water record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get aggregated dynamic monthly summary for a given user (nic),
     * matching the legacy "bill" schema so the Flutter app remains backward compatible.
     */
    public function summary(Request $request, $nic)
    {
        try {
            $user = User::where('nic', $nic)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User with this NIC does not exist'
                ], 404);
            }

            $userStatus = strtolower($user->status ?? 'connected');
            $year = $request->query('year', date('Y'));

            $records = WaterRecord::where('nic', $nic)
                ->whereYear('date', $year)
                ->get();

            $months = [
                1 => 'january', 2 => 'february', 3 => 'march', 4 => 'april',
                5 => 'may', 6 => 'june', 7 => 'july', 8 => 'august',
                9 => 'september', 10 => 'october', 11 => 'november', 12 => 'december'
            ];

            // Initialize structure matching the legacy `bill` schema
            $summary = [
                'id'              => (int)$nic, // use user's nic as a surrogate bill ID
                'nic'             => (int)$nic,
                'status'          => $userStatus,
                'is_connected'    => ($userStatus === 'connected'),
                'is_disconnected' => ($userStatus === 'disconnected'),
            ];

            foreach ($months as $num => $name) {
                $summary[$name . '_point'] = '0';
                $summary[$name . '_bill'] = '0';
            }

            $totalPoints = 0.0;
            $totalBill = 0.0;

            foreach ($records as $record) {
                // Carbon parses 'date' automatically due to casts
                $monthNum = (int)$record->date->format('n');
                $monthName = $months[$monthNum];

                $summary[$monthName . '_point'] = (string)round((float)$summary[$monthName . '_point'] + $record->points, 2);
                $summary[$monthName . '_bill'] = (string)round((float)$summary[$monthName . '_bill'] + $record->bill, 2);

                $totalPoints += $record->points;
                $totalBill += $record->bill;
            }

            $summary['total_points'] = (string)round($totalPoints, 2);
            $summary['total_bill'] = (string)round($totalBill, 2);
            
            // Set timestamps based on latest record, or now
            $latestRecord = $records->sortByDesc('updated_at')->first();
            $summary['created_at'] = $latestRecord ? $latestRecord->created_at->toIso8601String() : now()->toIso8601String();
            $summary['updated_at'] = $latestRecord ? $latestRecord->updated_at->toIso8601String() : now()->toIso8601String();

            return response()->json([
                'success' => true,
                'data' => $summary
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate monthly summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Return water usage context data for the Flutter app to call Gemini directly.
     * No Gemini API call is made here — Laravel only prepares and returns the data.
     */
    public function predict($nic)
    {
        try {
            $user = User::where('nic', $nic)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $records = WaterRecord::where('nic', $nic)
                ->orderBy('date', 'desc')
                ->get();

            if ($records->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No historical water records found for this user.'
                ], 400);
            }

            $now = \Carbon\Carbon::now();

            // ── Last month reference ────────────────────────────────────────
            $lastMonthDate   = $now->copy()->subMonth();
            $lastMonthRecord = WaterRecord::where('nic', $nic)
                ->whereYear('date', $lastMonthDate->year)
                ->whereMonth('date', $lastMonthDate->month)
                ->orderBy('date', 'desc')
                ->first();

            if (!$lastMonthRecord) {
                $lastMonthRecord = WaterRecord::where('nic', $nic)
                    ->where(function($q) use ($now) {
                        $q->whereYear('date', '<', $now->year)
                          ->orWhere(function($q2) use ($now) {
                              $q2->whereYear('date', $now->year)
                                 ->whereMonth('date', '<', $now->month);
                          });
                    })
                    ->orderBy('date', 'desc')
                    ->first();
            }

            $isActualLastMonth = $lastMonthRecord &&
                $lastMonthRecord->date->year  === $lastMonthDate->year &&
                $lastMonthRecord->date->month === $lastMonthDate->month;

            $lastMonthLabel = $lastMonthRecord
                ? $lastMonthRecord->date->format('M Y') . ($isActualLastMonth ? ' (Last Month)' : ' (Most Recent)')
                : null;
            $lastMonthUnits = $lastMonthRecord ? $lastMonthRecord->points : null;

            // ── Same-month history from previous years ──────────────────────
            $sameMonthRecords = WaterRecord::where('nic', $nic)
                ->whereMonth('date', $now->month)
                ->whereYear('date', '<', $now->year)
                ->orderBy('date', 'desc')
                ->get();

            $sameMonthHistoryList = [];
            $sameMonthHistoryText = "";
            foreach ($sameMonthRecords as $smRecord) {
                $monthLabel = $smRecord->date->format('M Y');
                $units = (float)$smRecord->points;
                $sameMonthHistoryList[] = [
                    'label' => $monthLabel,
                    'units' => $units,
                    'year'  => $smRecord->date->year,
                ];
                $sameMonthHistoryText .= "- {$monthLabel}: {$units} units\n";
            }

            $sameMonthAvg            = $sameMonthRecords->isNotEmpty() ? round($sameMonthRecords->avg('points'), 2) : null;
            $sameMonthLastYearRecord = $sameMonthRecords->first();
            $sameMonthLastYearLabel  = $sameMonthLastYearRecord ? $sameMonthLastYearRecord->date->format('M Y') : null;
            $sameMonthLastYearUnits  = $sameMonthLastYearRecord ? $sameMonthLastYearRecord->points : null;

            // ── Current month partial usage ─────────────────────────────────
            $currentMonthUsageSoFar = WaterRecord::where('nic', $nic)
                ->whereYear('date',  $now->year)
                ->whereMonth('date', $now->month)
                ->sum('points');

            $daysElapsed   = (int)$now->format('j');
            $daysInMonth   = (int)$now->daysInMonth;
            $daysRemaining = $daysInMonth - $daysElapsed;

            // ── Full history text (last 24 records) ─────────────────────────
            $historyText = "";
            foreach ($records->take(24) as $record) {
                $historyText .= "- " . $record->date->format('M Y') . ": {$record->points} units\n";
            }

            $historicalAvg  = round((float)$records->avg('points'), 2);
            $targetMonthStr = $now->format('F Y');

            // ── Return raw context data — Flutter calls Gemini directly ──────
            return response()->json([
                'success' => true,
                'data'    => [
                    // Prompt context fields
                    'target_month'             => $targetMonthStr,
                    'today'                    => $now->format('d F Y'),
                    'days_elapsed'             => $daysElapsed,
                    'days_in_month'            => $daysInMonth,
                    'days_remaining'           => $daysRemaining,
                    'current_month_usage'      => (float)$currentMonthUsageSoFar,
                    'historical_avg'           => $historicalAvg,
                    'same_month_avg'           => $sameMonthAvg,
                    'history_text'             => $historyText,
                    'same_month_history_text'  => $sameMonthHistoryText,
                    'same_month_name'          => $now->format('F'),
                    // UI display reference fields
                    'last_month'               => $lastMonthLabel,
                    'last_month_units'         => $lastMonthUnits,
                    'same_month_last_year'     => $sameMonthLastYearLabel,
                    'same_month_last_year_units' => $sameMonthLastYearUnits,
                    'same_month_history'       => $sameMonthHistoryList,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch prediction context',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
