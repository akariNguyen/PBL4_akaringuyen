<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'category_id',
        'name',
        'description',
        'price',
        'quantity',
        'status',
        'images',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    protected $casts = [
        'images' => 'array',
    ];
    public function reviews() {
    return $this->hasMany(Review::class);
    }

    public function averageRating() {
        return $this->reviews()->avg('rating');
    }
    protected static function booted()
    {
        static::saving(function ($product) {
            if ($product->quantity <= 0) {
                $product->status = 'out_of_stock';
            } elseif ($product->status === 'out_of_stock' && $product->quantity > 0) {
                $product->status = 'in_stock';
            }
        });
    }
}


