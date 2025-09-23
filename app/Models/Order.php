<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Order extends Model
{
    protected $fillable = [
        'code', 'buyer_name', 'buyer_email', 'payment_method', 'status',
        'subtotal', 'total', 'currency', 'meta',
    ];

    protected $casts = ['meta' => 'array'];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function attendees(): HasManyThrough
    {
        return $this->hasManyThrough(
            OrderItemAttendee::class, // Final
            OrderItem::class,         // Intermedio
            'order_id',               // FK en order_items que apunta a orders
            'order_item_id',          // FK en order_item_attendees que apunta a order_items
            'id',                     // Local key en orders
            'id'                      // Local key en order_items
        );
    }
}
