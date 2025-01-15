<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function getCountries()
    {
     // Fetch all countries and sort them by name
     $countries = Country::where('name', '!=', 'United States')->get()->sortBy('name');
  
     // Find the United States and prepend it to the collection
     $unitedStates = Country::where('name', 'United States')->first();
     $countries->prepend($unitedStates);
     
     return $countries;
    }
}
