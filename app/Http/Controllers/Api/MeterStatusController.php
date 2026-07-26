<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MeterStatusController extends Controller
{
    /**
     * Check or update meter connection status by NIC.
     * 
     * POST /api/meter/status
     * Body:
     * {
     *    "nic": 199912345678,
     *    "status": "disconnected" // (optional: "connected" or "disconnected" to update)
     * }
     */
    public function checkOrUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nic' => 'required',
            'status' => 'nullable|string|in:connected,disconnected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('nic', $request->nic)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found for the provided NIC.'
                ], 404);
            }

            // If status is provided in request, update user's status in DB & trigger FCM notification
            $previousStatus = strtolower($user->status ?? 'connected');
            if ($request->filled('status')) {
                $newStatus = strtolower($request->status);
                $user->status = $newStatus;
                $user->save();

                \Illuminate\Support\Facades\Log::info("Meter status updated for user NIC {$user->nic}: {$previousStatus} -> {$newStatus}");

                // Send FCM push notification if user has FCM token
                if (!empty($user->fcm_token)) {
                    $isDisconnected = ($newStatus === 'disconnected');
                    $title = $isDisconnected ? "🔴 Water Meter Disconnected!" : "🟢 Water Meter Connected!";
                    $body = $isDisconnected
                        ? "Your water meter (Bill No: {$user->bill_no}) status is set to Disconnected."
                        : "Your water meter (Bill No: {$user->bill_no}) has been reconnected.";

                    try {
                        $fcmResult = \App\Services\FirebaseService::sendNotification(
                            $user->fcm_token,
                            $title,
                            $body,
                            [
                                'type'   => 'meter_status_change',
                                'status' => $newStatus,
                                'nic'    => (string)$user->nic,
                            ]
                        );
                        \Illuminate\Support\Facades\Log::info("FCM Notification sent for NIC {$user->nic}. Result: " . json_encode($fcmResult));
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to send meter status FCM notification: " . $e->getMessage());
                    }
                } else {
                    \Illuminate\Support\Facades\Log::warning("Cannot send FCM notification: User NIC {$user->nic} has no fcm_token saved in database.");
                }
            }

            $currentStatus = strtolower($user->status ?? 'connected');
            $isDisconnected = ($currentStatus === 'disconnected');

            return response()->json([
                'success'         => true,
                'nic'             => $user->nic,
                'name'            => $user->name,
                'bill_no'         => $user->bill_no,
                'status'          => $currentStatus,
                'is_connected'    => !$isDisconnected,
                'is_disconnected' => $isDisconnected,
                'message'         => $isDisconnected ? 'Meter is disconnected' : 'Meter is connected'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process meter status',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET endpoint to check meter status via URL parameter: /api/meter/status/{nic}
     */
    public function showStatus($nic)
    {
        try {
            $user = User::where('nic', $nic)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found for the provided NIC.'
                ], 404);
            }

            $currentStatus = strtolower($user->status ?? 'connected');
            $isDisconnected = ($currentStatus === 'disconnected');

            return response()->json([
                'success'         => true,
                'nic'             => $user->nic,
                'name'            => $user->name,
                'bill_no'         => $user->bill_no,
                'status'          => $currentStatus,
                'is_connected'    => !$isDisconnected,
                'is_disconnected' => $isDisconnected,
                'message'         => $isDisconnected ? 'Meter is disconnected' : 'Meter is connected'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check meter status',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
