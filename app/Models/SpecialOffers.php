<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialOffers extends Model
{
    protected $table = 'special_offers_tables';
    use HasFactory;

    protected $fillable = [
        'description',
        'image',
        'product_id',
    ];

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }
}
