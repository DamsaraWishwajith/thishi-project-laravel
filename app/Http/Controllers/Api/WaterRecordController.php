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
            'water_rate' => 'required|numeric|min:0',
            'points' => 'required|numeric|min:0',
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
            $waterRate = (float)$request->water_rate;
            $points = (float)$request->points;
            
            // Calculate bill if not explicitly provided
            $bill = $request->has('bill') ? (float)$request->bill : ($points * $waterRate);

            // Create or update the record for the specific user and date
            $record = WaterRecord::updateOrCreate(
                [
                    'nic' => $nic,
                    'date' => $date
                ],
                [
                    'water_rate' => $waterRate,
                    'points' => $points,
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
            $userExists = User::where('nic', $nic)->exists();
            if (!$userExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'User with this NIC does not exist'
                ], 404);
            }

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
                'id' => (int)$nic, // use user's nic as a surrogate bill ID
                'nic' => (int)$nic,
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

                $summary[$monthName . '_point'] = (string)((float)$summary[$monthName . '_point'] + $record->points);
                $summary[$monthName . '_bill'] = (string)((float)$summary[$monthName . '_bill'] + $record->bill);

                $totalPoints += $record->points;
                $totalBill += $record->bill;
            }

            $summary['total_points'] = (string)$totalPoints;
            $summary['total_bill'] = (string)$totalBill;
            
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
     * Generate prediction using Gemini API.
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

            // Get all water records ordered by date descending
            $records = WaterRecord::where('nic', $nic)
                ->orderBy('date', 'desc')
                ->get();

            if ($records->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No historical water records found for this user.'
                ], 400);
            }

            // ── Key reference points ────────────────────────────────────────
            $now = \Carbon\Carbon::now();

            // Most recent completed month before the current month
            // Try the actual last calendar month first, otherwise use the most recent available record
            $lastMonthDate   = $now->copy()->subMonth();
            $lastMonthRecord = WaterRecord::where('nic', $nic)
                ->whereYear('date', $lastMonthDate->year)
                ->whereMonth('date', $lastMonthDate->month)
                ->orderBy('date', 'desc')
                ->first();

            // If no record for last calendar month, find the most recent record before this month
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

            // Build a smart label: "Last Month" only if it's actually last month, else show the real month
            $isActualLastMonth = $lastMonthRecord &&
                $lastMonthRecord->date->year  === $lastMonthDate->year &&
                $lastMonthRecord->date->month === $lastMonthDate->month;

            $lastMonthLabel = $lastMonthRecord
                ? $lastMonthRecord->date->format('M Y') . ($isActualLastMonth ? ' (Last Month)' : ' (Most Recent)')
                : null;
            $lastMonthUnits = $lastMonthRecord ? $lastMonthRecord->points : null;

            // Same month last year = July 2025 (current month 1 year ago)
            $predictedMonthLastYear = $now->copy()->subYear();
            $sameMonthLastYearRecord = WaterRecord::where('nic', $nic)
                ->whereYear('date', $predictedMonthLastYear->year)
                ->whereMonth('date', $predictedMonthLastYear->month)
                ->orderBy('date', 'desc')
                ->first();
            $sameMonthLastYearLabel = $sameMonthLastYearRecord
                ? $sameMonthLastYearRecord->date->format('M Y')
                : null;
            $sameMonthLastYearUnits = $sameMonthLastYearRecord
                ? $sameMonthLastYearRecord->points
                : null;

            // ── Build history text for prompt ───────────────────────────────
            $historyText = "";
            $limitedRecords = $records->take(24);
            foreach ($limitedRecords as $record) {
                $monthStr = $record->date->format('M Y');
                $historyText .= "- Month: {$monthStr}, Usage: {$record->points} units\n";
            }

            // ── Compute current month to predict ────────────────────────────
            $targetMonthStr  = $now->format('F Y');   // July 2026
            $currentMonthStr = $now->format('F Y');

            // Current month partial usage so far (sum of all records this month)
            $currentMonthUsageSoFar = WaterRecord::where('nic', $nic)
                ->whereYear('date',  $now->year)
                ->whereMonth('date', $now->month)
                ->sum('points');

            $daysElapsed   = (int)$now->format('j');       // day of month so far (e.g. 3)
            $daysInMonth   = (int)$now->daysInMonth;       // total days in July (31)
            $daysRemaining = $daysInMonth - $daysElapsed;

            // Same month last year (July 2025)
            $sameMonthLastYearForCurrent = $now->copy()->subYear();

            // ── Build Gemini prompt ─────────────────────────────────────────
            $prompt  = "You are an expert AI model analyzing household water consumption.\n";
            $prompt .= "Today is {$now->format('d F Y')} (day {$daysElapsed} of {$daysInMonth}).\n";
            $prompt .= "The user has used {$currentMonthUsageSoFar} units so far this month ({$currentMonthStr}).\n";
            $prompt .= "There are {$daysRemaining} days remaining in the month.\n\n";
            $prompt .= "KEY REFERENCE POINTS:\n";
            if ($lastMonthLabel && $lastMonthUnits !== null) {
                $prompt .= "- {$lastMonthLabel} usage: {$lastMonthUnits} units\n";
            }
            if ($sameMonthLastYearUnits !== null) {
                $prompt .= "- Same month last year ({$sameMonthLastYearLabel}) actual usage: {$sameMonthLastYearUnits} units\n";
            }
            $prompt .= "\nFull historical data (most recent first):\n{$historyText}\n";
            $prompt .= "The current water rate is 50.00 Rs per unit.\n\n";
            $prompt .= "Based on the partial usage so far ({$currentMonthUsageSoFar} units in {$daysElapsed} days) and historical patterns,\n";
            $prompt .= "predict the TOTAL usage for the entire month of {$targetMonthStr} ({$daysInMonth} days).\n";
            $prompt .= "Response format: You MUST return ONLY a raw JSON object — no markdown, no backticks.\n";
            $prompt .= "{\n";
            $prompt .= "  \"predicted_month\": \"{$targetMonthStr}\",\n";
            $prompt .= "  \"predicted_units\": 20,\n";
            $prompt .= "  \"predicted_bill\": 1000,\n";
            $prompt .= "  \"explanation\": \"Brief friendly explanation referencing partial usage so far and historical trends.\"\n";
            $prompt .= "}\n";

            // ── Call Gemini API with Fallback ─────────────────────────────────────────────
            $predictionData = null;
            $apiKey = env('GEMINI_API_KEY');
            $url    = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

            try {
                // Increase PHP execution time limit for long-running LLM calls
                set_time_limit(120);

                $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->timeout(90)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url, [
                        'contents' => [[
                            'parts' => [['text' => $prompt]]
                        ]]
                    ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $text   = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

                    // Strip markdown wrappers if present
                    $text = trim($text);
                    if (str_starts_with($text, '```')) {
                        $text = preg_replace('/^```(?:json)?|```$/m', '', $text);
                        $text = trim($text);
                    }

                    $predictionData = json_decode($text, true);
                } else {
                    \Illuminate\Support\Facades\Log::warning("Gemini API prediction request failed with status " . $response->status() . ": " . $response->body());
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to connect or fetch from Gemini API: " . $e->getMessage());
            }

            // If Gemini failed or didn't return valid data, run the smart fallback predictor
            if (!$predictionData) {
                \Illuminate\Support\Facades\Log::info("Running smart fallback water usage predictor for user NIC: {$nic}");
                
                $historicalAvg = $records->avg('points') ?: 20.0;
                
                // Project based on current month so far
                if ($daysElapsed > 0 && $currentMonthUsageSoFar > 0) {
                    $dailyAverage = $currentMonthUsageSoFar / $daysElapsed;
                    $projectedUnits = $currentMonthUsageSoFar + ($dailyAverage * $daysRemaining);
                    
                    // Blend projection and historical average based on how far we are into the month
                    $progressRatio = $daysElapsed / $daysInMonth;
                    $predictedUnits = ($projectedUnits * $progressRatio) + ($historicalAvg * (1 - $progressRatio));
                } else {
                    $predictedUnits = $historicalAvg;
                }
                
                // Round values
                $predictedUnits = round($predictedUnits, 1);
                $predictedBill = round($predictedUnits * 50.0, 2);
                
                $fallbackExplanation = "Your AI prediction is estimated based on your average consumption. So far, you have used {$currentMonthUsageSoFar} units in {$daysElapsed} days. Based on this rate and your historical average of " . round($historicalAvg, 1) . " units, we predict a total of {$predictedUnits} units for {$targetMonthStr}.";

                $predictionData = [
                    'predicted_month' => $targetMonthStr,
                    'predicted_units' => $predictedUnits,
                    'predicted_bill'  => $predictedBill,
                    'explanation'     => $fallbackExplanation
                ];
            }

            // ── Build final response ────────────────────────────────────────
            return response()->json([
                'success' => true,
                'data'    => array_merge($predictionData, [
                    'last_month'                => $lastMonthLabel,
                    'last_month_units'          => $lastMonthUnits,
                    'same_month_last_year'       => $sameMonthLastYearLabel,
                    'same_month_last_year_units' => $sameMonthLastYearUnits,
                ])
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Prediction failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
