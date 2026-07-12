
// Add missing 2026 Feb-Jun water_records
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
            'message' => 'Inserted 2026 Feb-Jun water records.',
            'records' => $inserted,
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});
