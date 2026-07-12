<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Bill;
use App\Models\User;

class BillController extends Controller
{
    private function formatData($data)
    {
        return [
            'january_point' => isset($data['january_point']) ? (int)$data['january_point'] : 0,
            'january_bill' => isset($data['january_bill']) ? (float)$data['january_bill'] : 0,
            'february_point' => isset($data['february_point']) ? (int)$data['february_point'] : 0,
            'february_bill' => isset($data['february_bill']) ? (float)$data['february_bill'] : 0,
            'march_point' => isset($data['march_point']) ? (int)$data['march_point'] : 0,
            'march_bill' => isset($data['march_bill']) ? (float)$data['march_bill'] : 0,
            'april_point' => isset($data['april_point']) ? (int)$data['april_point'] : 0,
            'april_bill' => isset($data['april_bill']) ? (float)$data['april_bill'] : 0,
            'may_point' => isset($data['may_point']) ? (int)$data['may_point'] : 0,
            'may_bill' => isset($data['may_bill']) ? (float)$data['may_bill'] : 0,
            'june_point' => isset($data['june_point']) ? (int)$data['june_point'] : 0,
            'june_bill' => isset($data['june_bill']) ? (float)$data['june_bill'] : 0,
            'july_point' => isset($data['july_point']) ? (int)$data['july_point'] : 0,
            'july_bill' => isset($data['july_bill']) ? (float)$data['july_bill'] : 0,
            'august_point' => isset($data['august_point']) ? (int)$data['august_point'] : 0,
            'august_bill' => isset($data['august_bill']) ? (float)$data['august_bill'] : 0,
            'september_point' => isset($data['september_point']) ? (int)$data['september_point'] : 0,
            'september_bill' => isset($data['september_bill']) ? (float)$data['september_bill'] : 0,
            'october_point' => isset($data['october_point']) ? (int)$data['october_point'] : 0,
            'october_bill' => isset($data['october_bill']) ? (float)$data['october_bill'] : 0,
            'november_point' => isset($data['november_point']) ? (int)$data['november_point'] : 0,
            'november_bill' => isset($data['november_bill']) ? (float)$data['november_bill'] : 0,
            'december_point' => isset($data['december_point']) ? (int)$data['december_point'] : 0,
            'december_bill' => isset($data['december_bill']) ? (float)$data['december_bill'] : 0,
            'total_points' => isset($data['total_points']) ? (int)$data['total_points'] : 0,
            'total_bill' => isset($data['total_bill']) ? (float)$data['total_bill'] : 0
        ];
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nic' => 'required|integer|exists:users,nic',
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
            $formatted = $this->formatData($request->all());
            $formatted['nic'] = $nic;

            // Check if bill already exists for this NIC
            $bill = Bill::where('nic', $nic)->first();

            if ($bill) {
                // Update existing bill
                $updateData = [];
                foreach ($formatted as $key => $value) {
                    if ($request->has($key)) {
                        $updateData[$key] = $value;
                    }
                }
                if (!empty($updateData)) {
                    $bill->update($updateData);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Bill updated successfully',
                    'data' => $bill
                ], 200);
            } else {
                // Create new bill
                $bill = Bill::create($formatted);

                return response()->json([
                    'success' => true,
                    'message' => 'Bill created successfully',
                    'data' => $bill
                ], 201);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create/update bill',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($nic)
    {
        try {
            $bill = Bill::where('nic', $nic)->first();

            if (!$bill) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bill not found for this NIC'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $bill
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve bill',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $nic)
    {
        try {
            // Validate that NIC exists in users table
            $userExists = User::where('nic', $nic)->exists();
            if (!$userExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'User with this NIC does not exist'
                ], 404);
            }

            $bill = Bill::where('nic', $nic)->first();
            $isNew = false;
            $formatted = $this->formatData($request->all());

            if (!$bill) {
                // Create new record if doesn't exist
                $formatted['nic'] = $nic;
                $bill = Bill::create($formatted);
                $isNew = true;
            } else {
                // Update existing record - only update fields that were provided
                $updateData = [];
                foreach ($formatted as $key => $value) {
                    if ($request->has($key)) {
                        $updateData[$key] = $value;
                    }
                }
                if (!empty($updateData)) {
                    $bill->update($updateData);
                }
            }

            return response()->json([
                'success' => true,
                'message' => $isNew ? 'Bill created successfully' : 'Bill updated successfully',
                'data' => $bill
            ], $isNew ? 201 : 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update bill',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
