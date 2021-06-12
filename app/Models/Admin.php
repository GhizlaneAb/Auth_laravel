<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasFactory,Notifiable;

    protected $table='admins';
    protected $fillable=[
        'name',
        'login',
        'password',

    ];

    protected $hidden=[
        'password'
    ];
}
