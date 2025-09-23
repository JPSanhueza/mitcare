<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class OrderItemAttendee extends Model
{
    protected $fillable = [
        'order_item_id', 'course_id', 'name', 'email', 'status',
        'moodle_has_account', 'moodle_username', 'moodle_checked_at',
    ];

    protected $casts = [
        'moodle_has_account' => 'boolean',
        'moodle_checked_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id'); // si agregas una columna, no necesario ahora
    }

    // Atajo al pedido a través del item:
    // public function order(): HasOneThrough
    // {
    //     return $this->hasOneThrough(
    //         Order::class,
    //         OrderItem::class,
    //         'id',        // local key en order_items
    //         'id',        // local key en orders
    //         'order_item_id', // fk en attendees
    //         'order_id'       // fk en order_items
    //     );
    // }
}
