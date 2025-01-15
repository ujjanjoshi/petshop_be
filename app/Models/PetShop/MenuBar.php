<?php

namespace App\Models\PetShop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuBar extends Model
{
    use HasFactory;
    protected $connection = 'mysql_pet_shop';
    protected $table = 'menu_bars';

    protected $fillable = [
        'name',
        'url',
        'is_active'
    ];
}
