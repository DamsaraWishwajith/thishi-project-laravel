<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaterRecord extends Model
{
    protected $table = 'water_records';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nic',
        'date',
        'water_rate',
        'points',
        'bill',
        'paid',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'water_rate' => 'float',
        'points' => 'float',
        'bill' => 'float',
        'paid' => 'boolean',
    ];

    /**
     * Get the user that owns the water record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nic', 'nic');
    }

    /**
     * Boot the model to listen for save events.
     */
    protected static function booted()
    {
        static::saved(function ($record) {
            try {
                $nic = $record->nic;
                $user = User::where('nic', $nic)->first();
                if (!$user || !$user->fcm_token) {
                    return;
                }

                // Check if current month
                $recordDate = \Carbon\Carbon::parse($record->date);
                $now = \Carbon\Carbon::now();
                
                if ($recordDate->year !== $now->year || $recordDate->month !== $now->month) {
                    return;
                }

                $currentMonthStr = $now->format('Y-m');

                // Check if already alerted
                if ($user->last_usage_alert_month === $currentMonthStr) {
                    return;
                }

                // Get cumulative usage this month
                $cumulativeUsage = self::where('nic', $nic)
                    ->whereYear('date', $now->year)
                    ->whereMonth('date', $now->month)
                    ->sum('points');

                // Compute predicted usage
                $records = self::where('nic', $nic)
                    ->where('date', '<', $now->copy()->startOfMonth()->format('Y-m-d'))
                    ->get();
                $historicalAvg = $records->avg('points') ?: 20.0;

                $daysElapsed = (int)$now->format('j');
                $daysInMonth = (int)$now->daysInMonth;
                $daysRemaining = $daysInMonth - $daysElapsed;

                if ($daysElapsed > 0 && $cumulativeUsage > 0) {
                    $dailyAverage = ($cumulativeUsage - $record->points) / max(1, $daysElapsed - 1);
                    if ($daysElapsed > 1 && $dailyAverage > 0) {
                        $projectedUnits = ($cumulativeUsage - $record->points) + ($dailyAverage * ($daysRemaining + 1));
                        $progressRatio = ($daysElapsed - 1) / $daysInMonth;
                        $predictedUnits = ($projectedUnits * $progressRatio) + ($historicalAvg * (1 - $progressRatio));
                    } else {
                        $predictedUnits = $historicalAvg;
                    }
                } else {
                    $predictedUnits = $historicalAvg;
                }

                $predictedUnits = round($predictedUnits, 1);

                // If usage exceeds prediction, trigger push notification
                if ($cumulativeUsage > $predictedUnits) {
                    $title = "🚨 Water Usage Alert";
                    $body = "Your usage this month has reached " . round($cumulativeUsage, 1) . " units, which exceeds your AI predicted usage of " . $predictedUnits . " units. Please conserve water.";
                    
                    $sent = \App\Services\FirebaseService::sendNotification(
                        $user->fcm_token,
                        $title,
                        $body,
                        [
                            'type' => 'usage_alert',
                            'cumulative_usage' => (string)$cumulativeUsage,
                            'predicted_units' => (string)$predictedUnits
                        ]
                    );

                    if ($sent) {
                        $user->last_usage_alert_month = $currentMonthStr;
                        $user->saveQuietly();
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("FCM Boot Event Error: " . $e->getMessage());
            }
        });
    }
}
