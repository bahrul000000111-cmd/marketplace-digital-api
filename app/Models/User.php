<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // 1. Tambahkan import ini 
use App\Models\Product;

class User extends Authenticatable
{
    // 2. Tambahkan HasApiTokens ke dalam trait yang digunakan
    use HasApiTokens, HasFactory, Notifiable; 

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Role seller/buyer tetap di sini [cite: 36]
        'balance',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relasi: satu user (seller) punya banyak produk
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}