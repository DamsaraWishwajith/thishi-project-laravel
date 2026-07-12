<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\WaterRecord;
use App\Models\PaymentSlip;

class AdminController extends Controller
{
    /**
     * Helper to verify if the admin is logged in.
     */
    private function isAuthenticated(Request $request)
    {
        return $request->session()->get('admin_logged_in') === true;
    }

    /**
     * Show the login page.
     */
    public function login(Request $request)
    {
        if ($this->isAuthenticated($request)) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    /**
     * Handle login submission.
     */
    public function doLogin(Request $request)
    {
        $request->validate([
            'passcode' => 'required|string',
        ]);

        $configuredPasscode = env('ADMIN_PASSWORD', 'admin123');

        if ($request->passcode === $configuredPasscode) {
            $request->session()->put('admin_logged_in', true);
            return redirect()->route('admin.dashboard')->with('success', 'Logged in successfully.');
        }

        return back()->withErrors(['passcode' => 'Invalid passcode. Please try again.']);
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        $request->session()->forget('admin_logged_in');
        return redirect()->route('admin.login')->with('success', 'Logged out successfully.');
    }

    /**
     * Show admin dashboard (Stats and System Overview).
     */
    public function dashboard(Request $request)
    {
        if (!$this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        // Calculate statistics
        $totalUsers = User::count();
        $totalLiters = (float)WaterRecord::sum('points');
        $avgRate = (float)WaterRecord::avg('water_rate');
        $totalBilling = (float)WaterRecord::sum('bill');

        // Recent logs
        $recentReadings = WaterRecord::with('user')
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        $recentUsers = User::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers', 
            'totalLiters', 
            'avgRate', 
            'totalBilling',
            'recentReadings',
            'recentUsers'
        ));
    }

    /**
     * Show the dedicated, searchable list of all users.
     */
    public function userList(Request $request)
    {
        if (!$this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $search = $request->query('search');

        $users = User::when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nic', 'like', "%{$search}%")
                  ->orWhere('pno', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
        })
        ->paginate(15)
        ->withQueryString();

        return view('admin.user_list', compact('users', 'search'));
    }

    /**
     * Show all daily records in the database, with pagination and filters.
     */
    public function allRecords(Request $request)
    {
        if (!$this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $searchNic = $request->query('nic');
        $searchDate = $request->query('date');

        $records = WaterRecord::with('user')
            ->when($searchNic, function ($query, $searchNic) {
                $query->where('nic', 'like', "%{$searchNic}%");
            })
            ->when($searchDate, function ($query, $searchDate) {
                $query->whereDate('date', $searchDate);
            })
            ->orderBy('date', 'desc')
            ->paginate(30)
            ->withQueryString();

        return view('admin.all_records', compact('records', 'searchNic', 'searchDate'));
    }

    /**
     * Show monthly billing list for all users for a given month and year.
     */
    public function allSummaries(Request $request)
    {
        if (!$this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $year = $request->query('year', date('Y'));
        $month = $request->query('month', date('n'));
        $search = $request->query('search');

        $users = User::when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('nic', 'like', "%{$search}%");
        })->get();

        $records = WaterRecord::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        // Build summary map: grid[nic] = ['points' => X, 'bill' => Y, 'paid' => boolean, 'has_records' => boolean]
        $grid = [];
        foreach ($users as $user) {
            $grid[$user->nic] = [
                'points' => 0.0,
                'bill' => 0.0,
                'paid' => true,
                'has_records' => false
            ];
        }

        foreach ($records as $record) {
            if (isset($grid[$record->nic])) {
                $grid[$record->nic]['points'] += $record->points;
                $grid[$record->nic]['bill'] += $record->bill;
                $grid[$record->nic]['has_records'] = true;
                if (!$record->paid) {
                    $grid[$record->nic]['paid'] = false;
                }
            }
        }

        return view('admin.all_summaries', compact('users', 'grid', 'year', 'month', 'search'));
    }

    /**
     * Toggle the monthly payment status of a user's water records for a given month and year.
     */
    public function togglePaymentStatus(Request $request)
    {
        if (!$this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $request->validate([
            'nic' => 'required|integer|exists:users,nic',
            'year' => 'required|integer',
            'month' => 'required|integer|between:1,12',
            'status' => 'required|boolean',
        ]);

        $nic = $request->nic;
        $year = $request->year;
        $month = $request->month;
        $status = $request->status;

        // Fetch and update all records for that user in that month and year
        WaterRecord::where('nic', $nic)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->update(['paid' => $status]);

        $statusText = $status ? 'Paid' : 'Unpaid';
        return back()->with('success', "Payment status marked as {$statusText} successfully.");
    }

    /**
     * Dedicated page to display the user registration form.
     */
    public function createUser(Request $request)
    {
        if (!$this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }
        return view('admin.create_user');
    }

    /**
     * Show user details hub page with subpage navigation buttons.
     */
    public function userDetail(Request $request, $nic)
    {
        if (!$this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $user = User::where('nic', $nic)->firstOrFail();
        return view('admin.user_detail', compact('user'));
    }

    /**
     * Show the dedicated form page to log a daily water reading for a specific user.
     */
    public function createWaterRecord(Request $request, $nic)
    {
        if (!$this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $user = User::where('nic', $nic)->firstOrFail();
        return view('admin.create_water_record', compact('user'));
    }

    /**
     * Show the dedicated form page to log a daily water reading for any user.
     */
    public function createWaterRecordGlobal(Request $request)
    {
        if (!$this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $users = User::orderBy('name', 'asc')->get();
        return view('admin.create_water_record_global', compact('users'));
    }

    /**
     * Show the dedicated page for dynamic monthly billing summaries.
     */
    public function userSummary(Request $request, $nic)
    {
        if (!$this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $user = User::where('nic', $nic)->firstOrFail();
        
        $year = $request->query('year', date('Y'));
        
        $yearRecords = WaterRecord::where('nic', $nic)
            ->whereYear('date', $year)
            ->get();

        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        $monthlyAggregates = [];
        foreach ($months as $num => $name) {
            $monthlyAggregates[$name] = [
                'points' => 0.0,
                'bill' => 0.0
            ];
        }

        $totalPoints = 0.0;
        $totalBill = 0.0;

        foreach ($yearRecords as $record) {
            $monthNum = (int)$record->date->format('n');
            $monthName = $months[$monthNum];

            $monthlyAggregates[$monthName]['points'] += $record->points;
            $monthlyAggregates[$monthName]['bill'] += $record->bill;

            $totalPoints += $record->points;
            $totalBill += $record->bill;
        }

        return view('admin.user_summary', compact(
            'user', 
            'monthlyAggregates', 
            'totalPoints', 
            'totalBill',
            'year'
        ));
    }

    /**
     * Show the dedicated list of all daily records for a user.
     */
    public function waterRecordsList(Request $request, $nic)
    {
        if (!$this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $user = User::where('nic', $nic)->firstOrFail();
        
        $dailyRecords = WaterRecord::where('nic', $nic)
            ->orderBy('date', 'desc')
            ->get();

        return view('admin.water_records_list', compact('user', 'dailyRecords'));
    }

    /**
     * Create a new user from admin panel.
     */
    public function storeUser(Request $request)
    {
        if (!$this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $request->validate([
            'nic' => 'required|integer|unique:users,nic',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:191|unique:users,email',
            'pno' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'bill_no' => 'required|string|max:50',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'nic' => $request->nic,
            'name' => $request->name,
            'email' => $request->email,
            'pno' => $request->pno,
            'address' => $request->address,
            'bill_no' => $request->bill_no,
            'password' => bcrypt($request->password),
        ]);

        return redirect()->route('admin.users')->with('success', 'User registered successfully.');
    }

    /**
     * Store or update a daily record for a user via the admin form.
     */
    public function storeWaterRecord(Request $request)
    {
        if (!$this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $request->validate([
            'nic' => 'required|integer|exists:users,nic',
            'date' => 'required|date_format:Y-m-d',
            'water_rate' => 'required|numeric|min:0',
            'points' => 'required|numeric|min:0',
            'bill' => 'nullable|numeric|min:0',
        ]);

        $nic = $request->nic;
        $date = $request->date;
        $waterRate = (float)$request->water_rate;
        $points = (float)$request->points;
        $bill = $request->has('bill') && $request->bill !== null ? (float)$request->bill : ($points * $waterRate);

        WaterRecord::updateOrCreate(
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

        return redirect()->route('admin.records')->with('success', 'Water record saved successfully.');
    }

    /**
     * Delete a daily water record.
     */
    public function deleteWaterRecord(Request $request, $id)
    {
        if (!$this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $record = WaterRecord::findOrFail($id);
        $nic = $record->nic;
        $record->delete();

        // Redirect back to referring page or logical route
        if ($request->query('from') === 'all') {
            return redirect()->route('admin.records')->with('success', 'Water record deleted successfully.');
        }

        return redirect()->route('admin.users.records', $nic)->with('success', 'Water record deleted successfully.');
    }

    /**
     * Show meter status dashboard to manage disconnections.
     */
    public function meterDisconnect(Request $request)
    {
        if (!$this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $search = $request->query('search');

        $users = User::when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('nic', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
        })
        ->paginate(15)
        ->withQueryString();

        return view('admin.meter_disconnect', compact('users', 'search'));
    }

    /**
     * Toggle a user's water meter connection status.
     */
    public function toggleMeterStatus(Request $request)
    {
        if (!$this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $request->validate([
            'nic' => 'required|integer|exists:users,nic',
            'status' => 'required|string|in:connected,disconnected',
        ]);

        $user = User::where('nic', $request->nic)->firstOrFail();
        $user->update(['status' => $request->status]);

        $statusText = ucfirst($request->status);
        return back()->with('success', "Water meter for {$user->name} has been successfully {$statusText}.");
    }

    /**
     * Update a user's profile details.
     */
    public function updateUser(Request $request, $nic)
    {
        if (!$this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $user = User::where('nic', $nic)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:191|unique:users,email,' . $user->nic . ',nic',
            'pno' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'bill_no' => 'required|string|max:50',
            'password' => 'nullable|string|min:6',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'pno' => $request->pno,
            'address' => $request->address,
            'bill_no' => $request->bill_no,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        $user->update($updateData);

        return back()->with('success', 'User profile updated successfully.');
    }

    /**
     * Show uploaded payment slips for admin review.
     */
    public function paymentSlips(Request $request)
    {
        if (!$this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        // Auto-run migration if table doesn't exist or columns are missing
        if (!\Illuminate\Support\Facades\Schema::hasTable('payment_slips') || !\Illuminate\Support\Facades\Schema::hasColumn('payment_slips', 'bill_no')) {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        }

        $status = $request->query('status');
        $search = $request->query('search');

        $query = PaymentSlip::with('user');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nic', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $slips = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('admin.payment_slips', compact('slips', 'status', 'search'));
    }

    /**
     * Approve a payment slip and mark all corresponding month's water records as paid.
     */
    public function approvePaymentSlip(Request $request, $id)
    {
        if (!$this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $slip = PaymentSlip::findOrFail($id);
        $slip->update(['status' => 'approved']);

        // Mark all water records of this user for this month and year as paid
        WaterRecord::where('nic', $slip->nic)
            ->whereYear('date', $slip->year)
            ->whereMonth('date', $slip->month)
            ->update(['paid' => true]);

        return back()->with('success', "Payment slip approved. Monthly bill marked as paid.");
    }

    /**
     * Reject a payment slip.
     */
    public function rejectPaymentSlip(Request $request, $id)
    {
        if (!$this->isAuthenticated($request)) {
            return redirect()->route('admin.login');
        }

        $slip = PaymentSlip::findOrFail($id);
        $slip->update(['status' => 'rejected']);

        return back()->with('success', "Payment slip rejected.");
    }
}
