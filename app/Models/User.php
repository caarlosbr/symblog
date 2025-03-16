<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class User extends Eloquent
{
    protected $table = 'users';
    protected $fillable = ['id', 'nombre', 'user', 'email', 'password', 'perfil', 'created_at', 'updated_at'];
}
