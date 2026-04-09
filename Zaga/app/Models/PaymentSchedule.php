<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSchedule extends Model
{
    protected $fillable = [
        'order_id', 'order_item_id', 'due_date', 'amount',
        'principal', 'interest', 'remaining_balance',
        'paid', 'paid_date', 'paid_amount',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'paid_date' => 'date',
            'paid' => 'boolean',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function isOverdue(): bool
    {
        return !$this->paid && $this->due_date->isPast();
    }
}
