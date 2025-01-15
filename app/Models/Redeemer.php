<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Searchable;

class Redeemer extends Model
{
    use Searchable;

    /**
     * DB connection to petlink for sharing...
     */
    protected $connection = 'mysql_resource_db';
    protected $table = 'redeemers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'middle_name', 'last_name',
        'address', 'address2', 'city', 'state', 'country', 'zip', 'phone', 'mobile', 'email', 'vip'
    ];

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'first_name', 'last_name', 'email', 'phone'
    ];
    /**
     * All "purchased" certificates
     *
     */
    public function purchases()
    {
        return $this->hasMany('\App\Models\Certificate');
    }
    public function certificates()
    {
        return $this->hasMany('\App\Models\Certificate')
                    ->where('code', 'not like', 'GFTCARD%');
    }
    public function giftcards()
    {
        return $this->hasMany('\App\Models\GiftCard')
                    ->where('code', 'like', 'GFTCARD%');
    }
    public function user()
    {
        return $this->setConnection(null)->hasOne('\App\Models\User');
    }

    /**
     */
    public function getFullnameAttribute()
    {
        if (empty($this->middle_name))
            return $this->first_name .' '. $this->last_name;
        return $this->first_name .' '. $this->middle_name .' '. $this->last_name;
    }
    /**
     * New instance of User given user email
     *
     * @param  string    $email
     * @return App\Models\User $user or null
     */
    public static function userEmail(string $email)
    {
        if (empty($email)) {
            return null;
        }
        $redeemer = self::firstWhere('email', $email);
        if ($redeemer == null) {
            return null;
        }
        return $redeemer->user;
    }
}
