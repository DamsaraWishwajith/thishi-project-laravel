<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PaymentSlip;
use App\Models\User;

class PaymentSlipController extends Controller
{
    /**
     * Upload a new payment slip.
     */
    public function upload(Request $request)
    {
        // Auto-run migration if table doesn't exist or columns are missing
        if (!\Illuminate\Support\Facades\Schema::hasTable('payment_slips') || !\Illuminate\Support\Facades\Schema::hasColumn('payment_slips', 'bill_no')) {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        }

        $validator = Validator::make($request->all(), [
            'nic' => 'required|integer|exists:users,nic',
            'year' => 'required|integer',
            'month' => 'required|integer',
            'bill_no' => 'nullable|string|max:50',
            'amount' => 'nullable|numeric|min:0',
            'slip' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('slip');
            $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9\._-]/', '', $file->getClientOriginalName());
            
            // Create uploads directory if it doesn't exist
            $uploadPath = public_path('uploads/slips');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }
            
            $file->move($uploadPath, $fileName);
            $filePath = 'uploads/slips/' . $fileName;

            // Check if there is an existing pending or rejected slip for this month and overwrite it
            $slip = PaymentSlip::updateOrCreate(
                [
                    'nic' => $request->nic,
                    'year' => $request->year,
                    'month' => $request->month,
                ],
                [
                    'bill_no' => $request->bill_no,
                    'amount' => $request->amount ?? 0.00,
                    'slip_path' => $filePath,
                    'status' => 'pending',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment slip uploaded successfully',
                'data' => $slip
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload payment slip',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get slips status for a user.
     */
    public function index(Request $request, $nic)
    {
        try {
            // Auto-run migration if table doesn't exist or columns are missing
            if (!\Illuminate\Support\Facades\Schema::hasTable('payment_slips') || !\Illuminate\Support\Facades\Schema::hasColumn('payment_slips', 'bill_no')) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            }

            $userExists = User::where('nic', $nic)->exists();
            if (!$userExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $query = PaymentSlip::where('nic', $nic);

            if ($request->has('year')) {
                $query->where('year', $request->year);
            }
            if ($request->has('month')) {
                $query->where('month', $request->month);
            }

            $slips = $query->orderBy('updated_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $slips
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment slips',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
