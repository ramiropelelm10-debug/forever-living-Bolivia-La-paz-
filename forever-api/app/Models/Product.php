<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes; // Para que funcione el softDeletes que pusiste en la migración

    protected $fillable = [
    'sku', 
    'name', 
    'foto_persona', // <--- Asegúrate que diga esto y no 'image'
    'price_bs', 
    'cc_value', 
    'stock'
];
}