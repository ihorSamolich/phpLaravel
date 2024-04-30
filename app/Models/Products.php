<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'description', 'category_id', 'discount_percentage', 'discount_start_date', 'discount_end_date'];

    public function category()
    {
        return $this->belongsTo(Categories::class);
    }

    public function product_images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }
}
