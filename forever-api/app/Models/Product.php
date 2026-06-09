<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes; 

    protected $fillable = [
    'sku', 
    'name', 
    'foto_persona', 
    'price_bs', 
    'cc_value', 
    'stock'
];
}