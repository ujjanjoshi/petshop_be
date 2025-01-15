<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branding extends Model
{
    use HasFactory;
    protected $table = 'brandings';
    protected $fillable = [
        'header_logo',
        'footer_logo',
        'header_color',
        'footer_color',
        'address',
        'phone_number',
        "title",
        "fax",
        "email",
        "hours",
    ];
}
