<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Các field cho phép gán hàng loạt
     *
     * @var list<string>
     */
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

    /**
     * Các field ẩn khi trả về JSON
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Kiểu dữ liệu cần cast
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'role'              => 'string',
            'status'            => 'string',
        ];
    }

    /**
     * Quan hệ: User có nhiều Review
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Quan hệ: User có nhiều Address
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Quan hệ: User có 1 Address mặc định
     */
    public function defaultAddress()
    {
        return $this->hasOne(Address::class)->where('is_default', true);
    }

    /**
     * Quan hệ: User có 1 Shop
     */
    public function shop()
    {
        return $this->hasOne(Shop::class, 'user_id');
    }

    /**
     * Quan hệ: User có 1 Cart
     */
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Khi tạo user → tự động tạo Cart (nếu là customer)
     */
    protected static function booted()
    {
        static::created(function ($user) {
            if ($user->role === 'customer') {
                $user->cart()->create();
            }
        });
    }
}
