<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'code',
        'discount_amount',
        'expiry_date',
        'status',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'user_id');
    }

    public function isValid()
    {
        return $this->status === 'active' && $this->expiry_date->isFuture();
    }
}
