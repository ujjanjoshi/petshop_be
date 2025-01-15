<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuBar extends Model
{
    use HasFactory;
    protected $table = 'menu_bars';

    protected $fillable = [
        'name',
        'url',
        'is_active'
    ];
}
