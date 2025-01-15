<?php

namespace App\Models\PetShop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;
    protected $connection = 'mysql_pet_shop';
    protected $table = 'emails';
    protected $fillable = [
        'title',
        'subject',
        'body'
    ];
    public $timestamps = false;
}
