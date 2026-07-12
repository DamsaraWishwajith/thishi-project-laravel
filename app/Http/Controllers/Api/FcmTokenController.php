<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    /**
     * Store or update user's FCM device token
     */
    public function updateToken(Request $request)
    {
        $request->validate([
            'nic' => 'required',
            'token' => 'required|string',
        ]);

        $user = User::where('nic', $request->nic)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->fcm_token = $request->token;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'FCM token updated successfully'
        ], 200);
    }
}
