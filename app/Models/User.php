<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Redeemer;

class User extends Authenticatable
{
    use HasApiTokens,HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone_country_code',
        'phone',
        'email_code',
        'password',
        'status',
        'country_code',
        'address1',
        'address2',
        'company_name',
        'company_phone',
        'company_email',
        'company_commission',
        'note',
        'currency_id',
        'balance',
        'user_type',
        'state',
        'city',
        'postal_code',
        'email_send_datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Eloquent Relationship
     *
     */
    public function redeemer ()
    {
        return $this->belongsTo(Redeemer::class);
    }

    /**
     * Accessor & Mutator
     */
    public function getEmailAttribute()
    {
        return $this->redeemer->email ?? '';
    }
    public function getNameAttribute()
    {
        return $this->redeemer->fullname ?? '';
    }
}
