<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Country extends Model
{
    use HasFactory;
    protected $connection = 'mysql_resource_db';
    protected $table = 'countries';

    public static function getList()
    {
        return Cache::rememberForever('country_list', function() {

            // Fetch all countries and sort them by name
            $countries = Country::where('name', '!=', 'United States')->orderBy('name')->get();
            
            // Find the United States and prepend it to the collection
            $unitedStates = Country::where('name', 'United States')->first();
            $countries->prepend($unitedStates);
            
            return $countries;
        });
    }
}
