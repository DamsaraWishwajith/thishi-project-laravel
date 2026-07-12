<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

// Redirect /admin to /admin/dashboard
Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
});

// Admin Panel Routes
Route::get('/admin/login', [AdminController::class, 'login'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'doLogin'])->name('admin.login.submit');
Route::get('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

// NOTE: Static routes must be declared BEFORE routes with {nic} parameters to prevent routing conflicts
Route::get('/admin/users/create', [AdminController::class, 'createUser'])->name('admin.users.create');
Route::post('/admin/users', [AdminController::class, 'storeUser'])->name('admin.users.submit');
Route::get('/admin/records', [AdminController::class, 'allRecords'])->name('admin.records');
Route::get('/admin/summaries', [AdminController::class, 'allSummaries'])->name('admin.summaries');
Route::get('/admin/records/create', [AdminController::class, 'createWaterRecordGlobal'])->name('admin.records.create');
Route::get('/admin/users', [AdminController::class, 'userList'])->name('admin.users');

Route::get('/admin/users/{nic}', [AdminController::class, 'userDetail'])->name('admin.users.detail');
Route::post('/admin/users/{nic}/update', [AdminController::class, 'updateUser'])->name('admin.users.update');
Route::get('/admin/users/{nic}/records/create', [AdminController::class, 'createWaterRecord'])->name('admin.users.records.create');
Route::get('/admin/users/{nic}/summary', [AdminController::class, 'userSummary'])->name('admin.users.summary');
Route::get('/admin/users/{nic}/records', [AdminController::class, 'waterRecordsList'])->name('admin.users.records');

Route::post('/admin/water-records', [AdminController::class, 'storeWaterRecord'])->name('admin.water-records.submit');
Route::delete('/admin/water-records/{id}', [AdminController::class, 'deleteWaterRecord'])->name('admin.water-records.delete');
Route::post('/admin/payments/toggle', [AdminController::class, 'togglePaymentStatus'])->name('admin.payments.toggle');
Route::get('/admin/meter-disconnect', [AdminController::class, 'meterDisconnect'])->name('admin.meter-disconnect');
Route::post('/admin/meter-disconnect/toggle', [AdminController::class, 'toggleMeterStatus'])->name('admin.meter-disconnect.toggle');
Route::get('/admin/payment-slips', [AdminController::class, 'paymentSlips'])->name('admin.payment-slips');
Route::post('/admin/payment-slips/{id}/approve', [AdminController::class, 'approvePaymentSlip'])->name('admin.payment-slips.approve');
Route::post('/admin/payment-slips/{id}/reject', [AdminController::class, 'rejectPaymentSlip'])->name('admin.payment-slips.reject');

// Default home
Route::get('/', function () {
    return view('welcome');
});

// Temporary endpoint to seed dam@gmail.com data
Route::get('/seed-dam-user', function() {
    try {
        $user = \App\Models\User::updateOrCreate(
            ['email' => 'dam@gmail.com'],
            [
                'nic' => 987654321,
                'name' => 'Damika',
                'pno' => '0771234567',
                'address' => 'Colombo',
                'bill_no' => 'W-98765',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'status' => 'connected'
            ]
        );

        $data = [
            ['month' => 'Jun 2026', 'usage' => 19],
            ['month' => 'May 2026', 'usage' => 21],
            ['month' => 'Apr 2026', 'usage' => 17],
            ['month' => 'Mar 2026', 'usage' => 18],
            ['month' => 'Feb 2026', 'usage' => 20],
            ['month' => 'Jan 2026', 'usage' => 19],
            ['month' => 'Dec 2025', 'usage' => 18],
            ['month' => 'Nov 2025', 'usage' => 18],
            ['month' => 'Oct 2025', 'usage' => 17],
            ['month' => 'Sep 2025', 'usage' => 16],
            ['month' => 'Aug 2025', 'usage' => 21],
            ['month' => 'Jul 2025', 'usage' => 20],
            ['month' => 'Jun 2025', 'usage' => 18],
            ['month' => 'May 2025', 'usage' => 22],
            ['month' => 'Apr 2025', 'usage' => 18],
            ['month' => 'Mar 2025', 'usage' => 20],
            ['month' => 'Feb 2025', 'usage' => 20],
            ['month' => 'Jan 2025', 'usage' => 18],
            ['month' => 'Dec 2024', 'usage' => 19],
            ['month' => 'Nov 2024', 'usage' => 20],
            ['month' => 'Oct 2024', 'usage' => 19],
            ['month' => 'Sep 2024', 'usage' => 16],
            ['month' => 'Aug 2024', 'usage' => 20],
            ['month' => 'Jul 2024', 'usage' => 16],
            ['month' => 'Jun 2024', 'usage' => 18],
            ['month' => 'May 2024', 'usage' => 22],
            ['month' => 'Apr 2024', 'usage' => 16],
            ['month' => 'Mar 2024', 'usage' => 15],
            ['month' => 'Feb 2024', 'usage' => 16],
            ['month' => 'Jan 2024', 'usage' => 16],
            ['month' => 'Dec 2023', 'usage' => 14],
            ['month' => 'Nov 2023', 'usage' => 15],
            ['month' => 'Oct 2023', 'usage' => 16],
            ['month' => 'Sep 2023', 'usage' => 22],
            ['month' => 'Aug 2023', 'usage' => 17],
            ['month' => 'Jul 2023', 'usage' => 15],
            ['month' => 'Jun 2023', 'usage' => 15],
            ['month' => 'May 2023', 'usage' => 19],
            ['month' => 'Apr 2023', 'usage' => 17],
            ['month' => 'Mar 2023', 'usage' => 16],
            ['month' => 'Feb 2023', 'usage' => 15],
            ['month' => 'Jan 2023', 'usage' => 15],
        ];

        $waterRate = 50.0;
        $insertedCount = 0;

        // Delete all existing historical records for this NIC (except current month July 2026)
        // so we can re-insert with correct end-of-month dates
        \App\Models\WaterRecord::where('nic', $user->nic)
            ->where(function($q) {
                $q->whereYear('date', '<', 2026)
                  ->orWhere(function($q2) {
                      $q2->whereYear('date', 2026)->whereMonth('date', '<', 7);
                  });
            })
            ->delete();

        foreach ($data as $item) {
            // Store on the last day of each month: 2026-06-30, 2026-05-31, ..., 2023-01-31
            $date = \Carbon\Carbon::createFromFormat('M Y', $item['month'])->endOfMonth()->format('Y-m-d');

            \App\Models\WaterRecord::create([
                'nic'        => $user->nic,
                'date'       => $date,
                'water_rate' => $waterRate,
                'points'     => $item['usage'],
                'bill'       => $item['usage'] * $waterRate,
                'paid'       => true,
            ]);
            $insertedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Seeded user dam@gmail.com (NIC: {$user->nic}) with {$insertedCount} records successfully."
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Seeding failed: ' . $e->getMessage()
        ], 500);
    }
});

// Seed July 2026 daily data + full bill record for presentation
Route::get('/seed-july-data', function () {
    try {
        $nic = 987654321;
        $user = \App\Models\User::where('nic', $nic)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User dam@gmail.com not found. Run /seed-dam-user first.'], 404);
        }

        $waterRate = 50.0;

        // Generate one record for every day of July from 1st up to today
        $today    = \Carbon\Carbon::now();
        $startDay = \Carbon\Carbon::create(2026, 7, 1);

        $dailyUsage = [
            1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1,
            6 => 1, 7 => 1, 8 => 1, 9 => 1, 10 => 1,
            11 => 1, 12 => 1, 13 => 1, 14 => 1, 15 => 1,
            16 => 1, 17 => 1, 18 => 1, 19 => 1, 20 => 1,
        ];

        $insertedCount = 0;
        $totalUnits    = 0;

        $current = $startDay->copy();
        while ($current->lte($today) && $current->month === 7) {
            $day    = (int) $current->day;
            $points = $dailyUsage[$day] ?? 1;

            \App\Models\WaterRecord::updateOrCreate(
                ['nic' => $nic, 'date' => $current->format('Y-m-d')],
                [
                    'water_rate' => $waterRate,
                    'points'     => $points,
                    'bill'       => $points * $waterRate,
                    'paid'       => false,
                ]
            );

            $totalUnits += $points;
            $insertedCount++;
            $current->addDay();
        }

        $totalBillJuly = $totalUnits * $waterRate;

        // Update bill table with all 2026 data
        $billData = [
            'nic'            => $nic,
            'january_point'  => 19,  'january_bill'   => 950,
            'february_point' => 20,  'february_bill'  => 1000,
            'march_point'    => 18,  'march_bill'     => 900,
            'april_point'    => 17,  'april_bill'     => 850,
            'may_point'      => 21,  'may_bill'       => 1050,
            'june_point'     => 19,  'june_bill'      => 950,
            'july_point'     => $totalUnits, 'july_bill' => $totalBillJuly,
            'august_point'   => 0,   'august_bill'    => 0,
            'september_point'=> 0,   'september_bill' => 0,
            'october_point'  => 0,   'october_bill'   => 0,
            'november_point' => 0,   'november_bill'  => 0,
            'december_point' => 0,   'december_bill'  => 0,
            'total_points'   => 19 + 20 + 18 + 17 + 21 + 19 + $totalUnits,
            'total_bill'     => 950 + 1000 + 900 + 850 + 1050 + 950 + $totalBillJuly,
        ];
        \App\Models\Bill::updateOrCreate(['nic' => $nic], $billData);

        return response()->json([
            'success'       => true,
            'message'       => "Seeded July 2026 daily records from 2026-07-01 to {$today->format('Y-m-d')} ({$insertedCount} days, {$totalUnits} units total).",
            'days_inserted' => $insertedCount,
            'july_units'    => $totalUnits,
            'july_bill'     => $totalBillJuly,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed: ' . $e->getMessage()
        ], 500);
    }
});

// Add missing 2026 Feb-Jun water_records (run this if seed-dam-user missed them)
Route::get('/seed-2026-data', function () {
    try {
        $nic       = 987654321;
        $waterRate = 50.0;

        $months = [
            ['date' => '2026-02-28', 'points' => 20],
            ['date' => '2026-03-31', 'points' => 18],
            ['date' => '2026-04-30', 'points' => 17],
            ['date' => '2026-05-31', 'points' => 21],
            ['date' => '2026-06-30', 'points' => 19],
        ];

        $inserted = [];
        foreach ($months as $m) {
            \App\Models\WaterRecord::updateOrCreate(
                ['nic' => $nic, 'date' => $m['date']],
                [
                    'water_rate' => $waterRate,
                    'points'     => $m['points'],
                    'bill'       => $m['points'] * $waterRate,
                    'paid'       => true,
                ]
            );
            $inserted[] = $m['date'] . ' -> ' . $m['points'] . ' units';
        }

        return response()->json([
            'success' => true,
            'message' => 'Inserted 2026 Feb-Jun water records into water_records table.',
            'records' => $inserted,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed: ' . $e->getMessage()
        ], 500);
    }
});

// Route to run migrations dynamically
Route::get('/run-migrations', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();
        return response()->json([
            'success' => true,
            'message' => 'Migrations ran successfully.',
            'output' => $output
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Migration failed: ' . $e->getMessage()
        ], 500);
    }
});

// Route to manually test FCM push notification
Route::get('/test-fcm/{nic}', function ($nic) {
    try {
        $user = \App\Models\User::where('nic', $nic)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found']);
        }
        if (!$user->fcm_token) {
            return response()->json(['success' => false, 'message' => 'User has no FCM token registered']);
        }

        $sent = \App\Services\FirebaseService::sendNotification(
            $user->fcm_token,
            "🔥 Test Notification",
            "This is a test notification from the Water System app!"
        );

        return response()->json([
            'success' => $sent,
            'message' => $sent ? 'Notification sent successfully' : 'Failed to send notification',
            'token' => $user->fcm_token
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Exception: ' . $e->getMessage()
        ], 500);
    }
});
