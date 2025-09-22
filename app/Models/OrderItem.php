<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'course_id', 'course_name', 'unit_price', 'qty', 'subtotal', 'meta',
    ];

    protected $casts = ['meta' => 'array'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(OrderItemAttendee::class);
    }
}
