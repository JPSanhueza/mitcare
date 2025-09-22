<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
