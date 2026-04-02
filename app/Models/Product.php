<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id', 'category_id', 'title', 'description', 'price', 
        'rating', 'thumbnail', 'file_path', 'stock', 'download_count', 'status'
    ];

    // Relasi ke kategori
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    // Relasi ke seller
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}