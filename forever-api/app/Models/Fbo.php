<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fbo extends Model
{
    protected $fillable = [
        'fbo_id', 
        'name', 
        'last_name', 
        'discount_rate', 
        'level', 
        'dni', 
        'email'
    ];
}