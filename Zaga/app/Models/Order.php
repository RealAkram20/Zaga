<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'status', 'total_deposit',
        'total_full', 'payment_method', 'shipping_address', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentSchedules()
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    public function getOutstandingBalanceAttribute(): int
    {
        return $this->paymentSchedules()
            ->where('paid', false)
            ->sum('amount');
    }
}
