<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bill extends Model
{
    protected $table = 'bill';

    /**
     * Get the user associated with the bill.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nic', 'nic');
    }

    protected $fillable = [
        'nic',

        'january_point',
        'january_bill',
        'february_point',
        'february_bill',
        'march_point',
        'march_bill',
        'april_point',
        'april_bill',
        'may_point',
        'may_bill',
        'june_point',
        'june_bill',
        'july_point',
        'july_bill',
        'august_point',
        'august_bill',
        'september_point',
        'september_bill',
        'october_point',
        'october_bill',
        'november_point',
        'november_bill',
        'december_point',
        'december_bill',

        'total_points',
        'total_bill'
    ];
}
