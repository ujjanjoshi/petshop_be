<?php

namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens,HasFactory;
    protected $table = 'admins';
    protected $fillable = [  
        'name',
        'email',
        'password',
        'is_super',
        'created_at',
        'updated_at',
    ];
}
