<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSlip extends Model
{
    use HasFactory;

    protected $fillable = [
        'nic',
        'year',
        'month',
        'bill_no',
        'amount',
        'slip_path',
        'status',
    ];

    /**
     * Get the user that owns the payment slip.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nic', 'nic');
    }
}
