<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $table = 'user';

    protected $fillable = [
        'id_user',
        'email',
        'password',
        'name',
        'birthdate',
        'id_role',
        'link_foto',
        'izin_edit'
    ];

    protected $hidden = [
        'password'
    ];
}
