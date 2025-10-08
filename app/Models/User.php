<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar_path',
        'gender',
        'role',
        'status',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'role'              => 'string',
            'status'            => 'string',
        ];
    }

    // --- Quan hệ ---
    public function reviews()     { return $this->hasMany(Review::class); }
    public function addresses()   { return $this->hasMany(Address::class); }
    public function defaultAddress() { return $this->hasOne(Address::class)->where('is_default', true); }
    public function shop()        { return $this->hasOne(Shop::class, 'user_id'); }
    public function cart()        { return $this->hasOne(Cart::class); }

    /**
     * Sự kiện Model
     */
    protected static function booted()
    {
        // 🔹 Tự tạo giỏ hàng cho khách hàng mới
        static::created(function ($user) {
            if ($user->role === 'customer') {
                $user->cart()->create();
            }
        });

        // 🔹 Khi cập nhật user → nếu seller bị inactive thì shop bị suspended
        static::updating(function ($user) {
            if ($user->role === 'seller' && $user->isDirty('status') && $user->status === 'inactive') {
                $shop = $user->shop;
                if ($shop && $shop->status !== 'suspended') {
                    $shop->update(['status' => 'suspended']);
                }
            }
        });
    }
}
