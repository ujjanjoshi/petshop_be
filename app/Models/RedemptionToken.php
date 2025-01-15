<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class RedemptionToken extends SanctumPersonalAccessToken
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $table = 'personal_access_tokens';
}
