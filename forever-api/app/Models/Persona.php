<?php
namespace app\Models;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model {
    protected $fillable = ['nombres', 'apellidos', 'ci', 'telefono'];

    public function user() {
        return $this->hasOne(User::class);
    }
}