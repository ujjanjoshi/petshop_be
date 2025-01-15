<?php

namespace App\Http\Controllers;

use App\Models\FeatureRental;
use App\Models\UnitRental;
use App\Models\VacationRental;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class RentalController extends Controller
{
  public function viewMoreRentals(Request $request)
  {

    $property_id = $request->query('property_id');
    $rentals = VacationRental::with(['rentalImage', 'unitRental', 'availabilityRentals', 'rateRental'])->where('id', $property_id)->first();
    // dd($rentals->toArray());
    $availability = $rentals['availabilityRentals'];
    // dd($availability);
    $configuration_availabiltiy = $availability['availability'];
    $configuration_minStay = $availability['minStay'];
    $configuration_minStay = str_replace(',', '', $configuration_minStay);
    $configuration_changeOver = $availability['changeOver'];
    $configuration_minPriorNotify = $availability['minPriorNotify'];
    $rates = $rentals['rateRental'];
    $configuration_minPriorNotify  = str_replace(',', '',  $configuration_minPriorNotify);
    $beginDate = $availability['begin_date'];
    $endDate = $availability['end_date'];
    // dd($beginDate);
    $start = Carbon::parse($beginDate);
    $end = Carbon::parse($endDate);

    $dates_with_availability = [];
    $total_days = $start->diffInDays($end) + 1;


    // Check if the availability string length matches the number of days
    if (strlen($configuration_availabiltiy) < $total_days) {
      throw new Exception('Availability string is shorter than the date range.');
    }

    // Loop through the dates
    $index = 0;
    while ($start->lte($end)) {
      $has_rate = false;

      foreach ($rates as $rate) {
        $rate_begin_date = Carbon::parse($rate['begin_date']);
        $rate_end_date = Carbon::parse($rate['end_date']);
        $rate_amount = $rate['amount'];
        $rate_type = $rate['type'];
        // echo ($start.$rate_end_date);
        if ($start >= $rate_begin_date  && $start <= $rate_end_date) {
          $dates_with_availability[] = [
            'date' => $start->format('Y-m-d'),
            'availability' => $configuration_availabiltiy[$index],
            'minStay' => $configuration_minStay[$index],
            'changeOver' => $configuration_changeOver[$index],
            'minPriorNotify' => $configuration_minPriorNotify[$index],
            'rate_amount' => $rate_amount,
            'rate_type' => $rate_type,
          ];
          $has_rate = true;
          break;  // Rate found for this date, no need to check further rates
        }
      }

      if (!$has_rate) {
        $dates_with_availability[] = [
          'date' => $start->format('Y-m-d'),
          'availability' => $configuration_availabiltiy[$index],
          'minStay' => $configuration_minStay[$index],
          'changeOver' => $configuration_changeOver[$index],
          'minPriorNotify' => $configuration_minPriorNotify[$index],
          'rate_amount' => null,
          'rate_type' => null,
        ];
      }

      $start->addDay();
      $index++;
    }
    // Get the total count of dates
    $total_count = count($dates_with_availability);
    $rentals['available_date'] = $dates_with_availability;
    // return $rentals;
    $json = $rentals;
    return $json;
  }

  public function featureRentals()
  {
    Session::forget('city_array');
    $feature_rentals = FeatureRental::get();
    $rental_datas = [];
    foreach ($feature_rentals as $feature_rental) {
      $rentals = VacationRental::with(['rentalImage:image,rental_id'])->select(['name', 'id', 'address1'])->where('id', $feature_rental->rental_id)->first();

      $rentals['image'] = $rentals['rentalImage']['image']['image'][0]['uri'];
      $rental_datas[] = $rentals->toArray();
    }

    //  dd($rentals->toArray());
    $rental_data = $rental_datas;
    return $rental_data;
  }
  public function reservationCall(Request $request)
  {
    $property_id = $request->property_id;
    $numberOfAdults = $request->numberOfAdults;
    $numberOfChildren = $request->numberOfChildren;
    $numberOfPets = $request->numberOfPets;
    $beginDate = $request->beginDate;
    $endDate = $request->endDate;
    $url = "/rentals/" . $property_id . '/reserve';
    $queryParams = [
      'numberOfAdults' => $numberOfAdults,
      'numberOfChildren' => $numberOfChildren,
      'numberOfPets' => $numberOfPets,
      'beginDate' => $beginDate,
      'endDate' => $endDate,
    ];
    Session::put('reservations', $queryParams);
    $response = Http::petapi()->post($url, $queryParams);
    if ($response->failed()) {
      echo ("Error requesting... Abort");
    }

    // Data received successfully
    $json = $response->json();
    // dd($json);

    if ($json) {
      $total_amount = $json['quoteResponseDetails']['orderList']['order']['orderTotal'];
      Session::put('total_amount', $total_amount);
      return $json;
    } else {
      return 'Not Available';
    }
  }

  public function addToCart(Request $request)
  {
    Session::forget('cart_datas');
    $property_id = $request->query('property_id');
    $quantity = 1;
    $total_amount = (float) str_replace(',', '', number_format(floatval(Session::get('total_amount')), 2));
    $rental = VacationRental::where('id', $property_id)->select(['name'])->first();
    // dd($total_amount);
    $data = [
      "rate_key" => null,
      "hotel_id" => null,
      'ticket_id' => null,
      "product_title" => $rental->name,
      "sku" => null,
      "rental_id" => $property_id,
      "product_id" => null,
      "quantity" => $quantity,
      "retail_price" => $total_amount,
      "wholesale_price" => $total_amount,
      "timer" => null
    ];
    // dd($data);
    if (Session::has('cart_datas')) {
      if (count(Session::get('cart_datas')) == 0) {
        Session::forget('cart_datas');
      }

      $cartDatas = Session::get('cart_datas');
      $found = false;

      foreach ($cartDatas as $key => $item) {
        if ($item["rental_id"] == $property_id) {
          $found = true;
          break;
        }
      }

      if (!$found) {
        // dd('hlo');
        $cartDatas[] = $data;
        Session::put('cart_datas', $cartDatas);
      }
    } else {
      Session::put('cart_datas', [$data]);
    }
    // dd(Session::get('cart_datas'));
    return 'success';
  }

  public function getFilterRental()
  {
    $unit_rentals = UnitRental::select('unit')->get();
    $unique_feature = [];
    foreach ($unit_rentals as $unit_rental) {
      // dd($unit_rental['unit']['unit']['featureValues']['featureValue']);unique_feature
      if ($unit_rental['unit']['unit']['featureValues'] != null) {


        $feature_values = $unit_rental['unit']['unit']['featureValues']['featureValue'];
        foreach ($feature_values as $feature_value) {
          if (!in_array($feature_value['unitFeatureName'], $unique_feature)) {
            $unique_feature[] = $feature_value['unitFeatureName'];
          }
        }
      }
    }
    return $unique_feature;
    // dd($unique_feature);
  }

  public function afterFilter(Request $request)
  {
    $search = $request->query('search');

    $bathroom = $request->query('bathroom');
    $bedroom = $request->query('bedroom');
    $amenities = $request->query('amenities');
    $amenities = explode(',', $amenities);

    $is_amenities = false;
    $filter_rental = [];
    if ($search == null) {
      $search = Session::get('city_array');
      // dd($search);
      $rentals = VacationRental::with(['unitRental:unit,rental_id', 'rentalImage:image,rental_id'])->whereIn('city', $search)
        ->select([
          'id',
          'name',
          'address1',
          'address2',
          'zip',
          'city',
          'state',
          'country',
          'latitude',
          'longitude'
        ])->get();
    } else {
      $rentals = VacationRental::with(['unitRental:unit,rental_id', 'rentalImage:image,rental_id'])->Where('city', $search)
        ->orWhere('state', 'like', '%' . $search . '%')
        ->orWhere('country', 'like', '%' . $search . '%')->select([
          'id',
          'name',
          'address1',
          'address2',
          'zip',
          'city',
          'state',
          'country',
          'latitude',
          'longitude'
        ])->get();
    }


    // dd($rentals->toArray());
    foreach ($rentals as $rental) {
      $bedroom_count = count($rental['unitRental']['unit']['unit']['bedrooms']['bedroom']);
      $bathroom_count = count($rental['unitRental']['unit']['unit']['bathrooms']['bathroom']);
      $feature_values = $rental['unitRental']['unit']['unit']['featureValues'];

      if ($feature_values != null) {
        foreach ($feature_values['featureValue'] as $feature_value) {
          if (count($amenities)) {
            if (!in_array($feature_value['unitFeatureName'], $amenities)) {
              $is_amenities = true;
            }
          } else {
            $is_amenities = true;
          }
        }
      } else {
        $is_amenities = true;
      }
      if ($bedroom_count >= $bedroom && $bathroom_count >= $bathroom && $is_amenities) {
        $maxSleep = $rental['unitRental']['unit']['unit']['maxSleep'];
        $area = $rental['unitRental']['unit']['unit']['area'];
        $rental->bedroom = $bedroom_count;
        $rental->bathroom = $bathroom_count;
        $rental->maxSleep = $maxSleep;
        $rental->area = $area;
        if ($rental->rentalImage) {
          $rental->rentalImage = $rental->rentalImage['image']['image'][0];
        }
        $filter_rental[] = $rental->toArray();
      }
    }
    $page = $request->query('page', 1); // Get current page from request, default to 1 if not provided
    $perPage = 10; // Number of items per page

    // Convert the array into a Laravel Collection
    $filteredCollection = new Collection($filter_rental);

    // Paginate the collection
    $paginatedData = $filteredCollection->slice(($page - 1) * $perPage, $perPage)->all();

    // Create a LengthAwarePaginator instance
    $paginatedRentals = new LengthAwarePaginator($paginatedData, count($filteredCollection), $perPage, $page, [
      'path' => $request->url(),
      'query' => $request->query(),
    ]);

    // Optionally, you can customize the paginator further, like adding additional parameters
    return $paginatedRentals;
    //   return view('Rentals.afterSearch.rentals', compact('paginatedRentals'));
  }
  public function searchRental(Request $request)
  {
    $search = $request->query('search');
    // $search_result = [];

    $results = VacationRental::where('address1', 'like', '%' . $search . '%')
      ->orWhere('address2', 'like', '%' . $search . '%')
      ->orWhere('city', 'like', '%' . $search . '%')
      ->orWhere('state', 'like', '%' . $search . '%')
      ->orWhere('country', 'like', '%' . $search . '%')
      ->select(['city', 'state', 'country'])
      ->take(5)
      ->distinct()->get();
    return $results;
  }
  public function afterSearch(Request $request)
  {
    $search = $request->query('search');
    $adults = $request->query('adults');
    $child = $request->query('child');
    $pets = intval($request->query('pets'));
    // dd($pets);
    $sum = 0;
    if ($adults != null && $adults != 0) {
      $sum = intval($adults);
    }
    if ($child != null && $child != 0) {
      $sum =  intval($child);
    }
    if ($adults != null && $child != null  && $child != 0 && $adults != 0) {
      $sum = intval($adults) + intval($child);
    }

    // dd($sum);
    $start_date = $request->query('checkin');
    // dd($pets);
    // if ($start_date != null) {
    $start_date = DateTime::createFromFormat('Y-m-d', $start_date);
    //   if ($start_date) {
    //     $formattedDate = $start_date->format('Y-m-d');
    $start_date = Carbon::parse($start_date);
    //   }
    // }

    $end_date = $request->query('checkout');
    // if ($start_date != null) {
    $end_date = DateTime::createFromFormat('Y-m-d', $end_date);
    //   if ($end_date) {
    //     $formattedDate = $end_date->format('Y-m-d');
    $end_date = Carbon::parse($end_date);
    //   }
    // }
    $min_difference = null;
    if ($start_date != null) {
      $min_difference = intval(Carbon::now()->diffInDays($start_date)) + 1;
    }
    // dd($min_difference);
    $difference = null;
    if ($start_date != null) {
      $difference = $start_date->diffInDays($end_date) + 1;
    }
    // dd($difference);
    $valid = true;
    $max_sleep = true;
    $pet_allowed = true;

    $search_result = [];
    $results = VacationRental::with(['unitRental:unit,rental_id', 'rentalImage:image,rental_id', 'availabilityRentals'])
      ->Where('city', $search)
      ->orWhere('state', 'like', '%' . $search . '%')
      ->orWhere('country', 'like', '%' . $search . '%')
      ->select([
        'id',
        'name',
        'zip',
        'address1',
        'address2',
        'city',
        'state',
        'country',
        'latitude',
        'longitude'
      ])
      ->get();
    // dd($results->toArray());
    foreach ($results as $result) {
      if ($sum != null) {
        // dd($max_sleep);
        // dd(intval($result['unitRental']['unit']['unit']['maxSleep']) < $sum);
        if (intval($result['unitRental']['unit']['unit']['maxSleep']) < $sum) {

          $max_sleep = false;
        }
      }
      if ($pets != null && $pets != 0) {
        if ($result['unitRental']['unit']['unit']['featuresDescription']['texts']['text']['textValue']) {
          $feature_description = $result['unitRental']['unit']['unit']['featuresDescription']['texts']['text']['textValue'];
          // dd($feature_description);
          $feature_description = explode(',', trim($feature_description));

          $trimmed_features = array_map('trim', $feature_description);
          // dd($trimmed_features);
          if (in_array('Pets Not Allowed', $trimmed_features)) {
            // dd('hlo');
            $pet_allowed = false;
          }
        }
      }
      if ($start_date != null && $end_date != null) {
        $availability = $result['availabilityRentals'];
        // dd($availability);
        $min_stay = str_replace(',', '', $availability['minStay']);

        $min_prior = str_replace(',', '', $availability['minPriorNotify']);
        // dd($min_prior);
        $beginDate = $availability['begin_date'];
        $endDate = $availability['end_date'];
        $start = Carbon::parse($beginDate);
        $end = Carbon::parse($endDate);
        // dd($start <= $start_date);
        if ($start_date->gte($start) && $end_date->lte($end)) {
          // dd($start_date);

          $total_days = $start->diffInDays($end) + 1;
          // Check if the availability string length matches the number of days
          if (strlen($availability['availability']) < $total_days) {
            throw new Exception('Availability string is shorter than the date range.');
          }
          $valid = true;
          // Loop through the dates
          $index = 0;
          while ($start->lte($end)) {

            if ($start->isSameDay($start_date)) {
              $change_over = $availability['changeOver'][$index];
              // if (strtolower($change_over) != 'c' && strtolower($change_over) != 'i') {

              if (strtolower($change_over) == 'x' || strtolower($change_over) == 'o') {
                $valid = false;
              }
              if ($availability['availability'][$index] != 'Y') {
                $valid = false;
              }
              if (intval($min_stay[$index]) > intval($difference)) {
                $valid = false;
              }
              if (intval($min_prior[$index]) > $min_difference) {
                $valid = false;
              }
            }

            if ($start->isSameDay($end_date)) {
              //   echo($start);
              if ($availability['availability'][$index] != 'Y') {
                $valid = false;
              }

              $change_over = $availability['changeOver'][$index];

              if (strtolower($change_over) == 'x' || strtolower($change_over) == 'i') {
                $valid = false;
              }
            }


            $start->addDay();
            $index++;
          }
        }
      }
      if ($pet_allowed && $valid && $max_sleep) {
        $maxSleep = $result['unitRental']['unit']['unit']['maxSleep'];
        $area = $result['unitRental']['unit']['unit']['area'];
        $bedroom_count = count($result['unitRental']['unit']['unit']['bedrooms']['bedroom']);
        $bathroom_count = count($result['unitRental']['unit']['unit']['bathrooms']['bathroom']);
        $result->bedroom = $bedroom_count;
        $result->bathroom = $bathroom_count;
        $result->maxSleep = $maxSleep;
        $result->area = $area;

        if ($result->rentalImage != null) {
          $result->rentalImage = $result->rentalImage['image']['image'][0];
        }
        $search_result[] = $result->toArray();
        $valid = true;
        $pet_allowed = true;
        $max_sleep = true;
      }
      if ($start_date == null && $end_date == null && $pets == null && $sum == null) {
        // dd('hlo');
        // dd($result['unitRental']['unit']['unit']['maxSleep']);
        $maxSleep = $result['unitRental']['unit']['unit']['maxSleep'];
        $area = $result['unitRental']['unit']['unit']['area'];
        $bedroom_count = count($result['unitRental']['unit']['unit']['bedrooms']['bedroom']);
        $bathroom_count = count($result['unitRental']['unit']['unit']['bathrooms']['bathroom']);
        $result->bedroom = $bedroom_count;
        $result->bathroom = $bathroom_count;
        $result->maxSleep = $maxSleep;
        $result->area = $area;
        if ($result->rentalImage) {
          $result->rentalImage = $result->rentalImage['image']['image'][0];
        }
        $search_result[] = $result->toArray();
      }
    }
    $page = $request->query('page', 1); // Get current page from request, default to 1 if not provided
    $perPage = 10; // Number of items per page

    // Convert the array into a Laravel Collection
    $filteredCollection = new Collection($search_result);

    // Paginate the collection
    $paginatedData = $filteredCollection->slice(($page - 1) * $perPage, $perPage)->all();

    // Create a LengthAwarePaginator instance
    $paginatedRentals = new LengthAwarePaginator($paginatedData, count($filteredCollection), $perPage, $page, [
      'path' => $request->url(),
      'query' => $request->query(),
    ]);

    // Optionally, you can customize the paginator further, like adding additional parameters
    return $paginatedRentals;
    // Dump and die to see the paginated results
    // return  view('Rentals.afterSearch.rentals', compact('paginatedRentals'));
  }

  public function afterSearchArrayRental(Request $request)
  {
    $search = $request->query('search');
    $adults = $request->query('adults');
    $child = $request->query('child');
    $pets = $request->query('pets');
    // dd($pets);
    $sum = 0;
    if ($adults != null && $adults != 0) {
      $sum = $adults;
    }
    if ($child != null && $child != 0) {
      $sum = $child;
    }
    if ($adults != null && $child != null && $child != 0  && $adults != 0) {
      $sum = intval($adults) + intval($child);
    }
    // dd($sum);
    $start_date = $request->query('checkin');
    // dd($pets);
    // if ($start_date != null) {
    $start_date = DateTime::createFromFormat('Y-m-d', $start_date);
    //   if ($start_date) {
    //     $formattedDate = $start_date->format('Y-m-d');
    $start_date = Carbon::parse($start_date);
    //   }
    // }

    $end_date = $request->query('checkout');
    // if ($start_date != null) {
    $end_date = DateTime::createFromFormat('Y-m-d', $end_date);
    //   if ($end_date) {
    //     $formattedDate = $end_date->format('Y-m-d');
    $end_date = Carbon::parse($end_date);
    //   }
    // }
    $difference = null;
    if ($start_date != null) {
      $difference = $start_date->diffInDays($end_date);
    }
    // dd($difference);
    $valid = true;
    $max_sleep = true;
    $pet_allowed = true;

    $is_filter = $request->query('is_filter', false);
    $bathroom = $request->query('bathroom');
    $bedroom = $request->query('bedroom');
    $amenities = $request->query('amenities');
    $amenities = explode(',', $amenities);

    $is_amenities = false;
    $filter_rental = [];
    $min_difference = null;
    if ($start_date != null) {
      $min_difference = intval(Carbon::now()->diffInDays($start_date)) + 1;
    }
    Session::forget('city_array');
    $longitude = $request->query('longitude');
    $latitude = $request->query('latitude');
    $search_result = [];
    $longitude = explode(',', $longitude);
    $latitude = explode(',', $latitude);
    if (intval($longitude[0]) < 30) {
      $results = VacationRental::with(['unitRental:unit,rental_id', 'rentalImage:image,rental_id'])
        ->whereBetween('longitude', [ceil($latitude[1]), ceil($latitude[0])])
        ->whereBetween('latitude', [ceil($longitude[0]), ceil($longitude[1])])
        ->select([
          'id',
          'name',
          'zip',
          'address1',
          'address2',
          'city',
          'state',
          'country',
          'latitude',
          'longitude'
        ])
        ->get();
    } else {
      $results = VacationRental::with(['unitRental:unit,rental_id', 'rentalImage:image,rental_id'])
        ->where('latitude', '>=', $longitude[0])
        ->where('latitude', '<=', $longitude[1])
        ->where('longitude', '<=', $latitude[0])
        ->where('longitude', '>=', $latitude[1])
        ->select([
          'id',
          'name',
          'zip',
          'address1',
          'address2',
          'city',
          'state',
          'country',
          'latitude',
          'longitude'
        ])
        ->get();
    }
    // dd($results);
    $city = [];

    foreach ($results as $result) {
      if ($sum != null) {
        // dd($max_sleep);
        // dd(intval($result['unitRental']['unit']['unit']['maxSleep']) < $sum);
        if (intval($result['unitRental']['unit']['unit']['maxSleep']) < $sum) {

          $max_sleep = false;
        }
      }
      if ($pets != null && $pets != 0) {
        if ($result['unitRental']['unit']['unit']['featuresDescription']['texts']['text']['textValue']) {
          $feature_description = $result['unitRental']['unit']['unit']['featuresDescription']['texts']['text']['textValue'];
          // dd($feature_description);
          $feature_description = explode(',', trim($feature_description));

          $trimmed_features = array_map('trim', $feature_description);
          // dd($trimmed_features);
          if (in_array('Pets Not Allowed', $trimmed_features)) {
            // dd('hlo');
            $pet_allowed = false;
          }
        }
      }
      if ($start_date != null && $end_date != null) {
        $availability = $result['availabilityRentals'];
        // dd($availability);
        $min_stay = str_replace(',', '', $availability['minStay']);

        $min_prior = str_replace(',', '', $availability['minPriorNotify']);
        // dd($min_prior);
        $beginDate = $availability['begin_date'];
        $endDate = $availability['end_date'];
        $start = Carbon::parse($beginDate);
        $end = Carbon::parse($endDate);
        // dd($start <= $start_date);
        if ($start_date->gte($start) && $end_date->lte($end)) {
          // dd($start_date);

          $total_days = $start->diffInDays($end) + 1;
          // Check if the availability string length matches the number of days
          if (strlen($availability['availability']) < $total_days) {
            throw new Exception('Availability string is shorter than the date range.');
          }
          $valid = true;
          // Loop through the dates
          $index = 0;
          while ($start->lte($end)) {

            if ($start->isSameDay($start_date)) {
              $change_over = $availability['changeOver'][$index];
              // if (strtolower($change_over) != 'c' && strtolower($change_over) != 'i') {

              if (strtolower($change_over) == 'x' || strtolower($change_over) == 'o') {
                $valid = false;
              }
              if ($availability['availability'][$index] != 'Y') {
                $valid = false;
              }
              if (intval($min_stay[$index]) > intval($difference)) {
                $valid = false;
              }
              if (intval($min_prior[$index]) > $min_difference) {
                $valid = false;
              }
            }

            if ($start->isSameDay($end_date)) {
              //   echo($start);
              if ($availability['availability'][$index] != 'Y') {
                $valid = false;
              }

              $change_over = $availability['changeOver'][$index];

              if (strtolower($change_over) == 'x' || strtolower($change_over) == 'i') {
                $valid = false;
              }
            }


            $start->addDay();
            $index++;
          }
        }
      }
      if ($pet_allowed && $valid && $max_sleep) {
        $maxSleep = $result['unitRental']['unit']['unit']['maxSleep'];
        $area = $result['unitRental']['unit']['unit']['area'];
        $bedroom_count = count($result['unitRental']['unit']['unit']['bedrooms']['bedroom']);
        $bathroom_count = count($result['unitRental']['unit']['unit']['bathrooms']['bathroom']);
        $result->bedroom = $bedroom_count;
        $result->bathroom = $bathroom_count;
        $result->maxSleep = $maxSleep;
        $result->area = $area;

        if ($result->rentalImage != null) {
          $result->rentalImage = $result->rentalImage['image']['image'][0];
        }
        $search_result[] = $result->toArray();
        $valid = true;
        $pet_allowed = true;
        $max_sleep = true;
      }
      if ($start_date == null && $end_date == null && $pets == null && $sum == null) {
        // dd('hlo');
        // dd($result['unitRental']['unit']['unit']['maxSleep']);
        $maxSleep = $result['unitRental']['unit']['unit']['maxSleep'];
        $area = $result['unitRental']['unit']['unit']['area'];
        $bedroom_count = count($result['unitRental']['unit']['unit']['bedrooms']['bedroom']);
        $bathroom_count = count($result['unitRental']['unit']['unit']['bathrooms']['bathroom']);
        $result->bedroom = $bedroom_count;
        $result->bathroom = $bathroom_count;
        $result->maxSleep = $maxSleep;
        $result->area = $area;
        if ($result->rentalImage) {
          $result->rentalImage = $result->rentalImage['image']['image'][0];
        }
        $search_result[] = $result->toArray();
      }
    }
    // dd($search_result) ;
    $page = $request->query('page', 1); // Get current page from request, default to 1 if not provided
    $perPage = 10; // Number of items per page

    // Convert the array into a Laravel Collection
    $filteredCollection = new Collection($search_result);

    // Paginate the collection
    $paginatedData = $filteredCollection->slice(($page - 1) * $perPage, $perPage)->all();

    // Create a LengthAwarePaginator instance
    $paginatedRentals = new LengthAwarePaginator($paginatedData, count($filteredCollection), $perPage, $page, [
      'path' => $request->url(),
      'query' => $request->query(),
    ]);
    Session::put('city_array', $city);
    // Optionally, you can customize the paginator further, like adding additional parameters
    if ($is_filter) {
      if ($search == null) {
        $search = $city;
        // dd($search);
        $rentals = VacationRental::with(['unitRental:unit,rental_id', 'rentalImage:image,rental_id'])->whereIn('city', $search)
          ->select([
            'id',
            'name',
            'address1',
            'address2',
            'zip',
            'city',
            'state',
            'country',
            'latitude',
            'longitude'
          ])->get();
      } else {
        $rentals = VacationRental::with(['unitRental:unit,rental_id', 'rentalImage:image,rental_id'])->Where('city', $search)
          ->orWhere('state', 'like', '%' . $search . '%')
          ->orWhere('country', 'like', '%' . $search . '%')->select([
            'id',
            'name',
            'address1',
            'address2',
            'zip',
            'city',
            'state',
            'country',
            'latitude',
            'longitude'
          ])->get();
      }


      // dd($rentals->toArray());
      foreach ($rentals as $rental) {
        $bedroom_count = count($rental['unitRental']['unit']['unit']['bedrooms']['bedroom']);
        $bathroom_count = count($rental['unitRental']['unit']['unit']['bathrooms']['bathroom']);
        $feature_values = $rental['unitRental']['unit']['unit']['featureValues'];

        if ($feature_values != null) {
          foreach ($feature_values['featureValue'] as $feature_value) {
            if (count($amenities)) {
              if (!in_array($feature_value['unitFeatureName'], $amenities)) {
                $is_amenities = true;
              }
            } else {
              $is_amenities = true;
            }
          }
        } else {
          $is_amenities = true;
        }
        if ($bedroom_count >= $bedroom && $bathroom_count >= $bathroom && $is_amenities) {
          $maxSleep = $rental['unitRental']['unit']['unit']['maxSleep'];
          $area = $rental['unitRental']['unit']['unit']['area'];
          $rental->bedroom = $bedroom_count;
          $rental->bathroom = $bathroom_count;
          $rental->maxSleep = $maxSleep;
          $rental->area = $area;
          if ($rental->rentalImage) {
            $rental->rentalImage = $rental->rentalImage['image']['image'][0];
          }
          $filter_rental[] = $rental->toArray();
        }
      }
      $page = $request->query('page', 1); // Get current page from request, default to 1 if not provided
      $perPage = 10; // Number of items per page

      // Convert the array into a Laravel Collection
      $filteredCollection = new Collection($filter_rental);

      // Paginate the collection
      $paginatedData = $filteredCollection->slice(($page - 1) * $perPage, $perPage)->all();

      // Create a LengthAwarePaginator instance
      $paginatedRentals = new LengthAwarePaginator($paginatedData, count($filteredCollection), $perPage, $page, [
        'path' => $request->url(),
        'query' => $request->query(),
      ]);
    }
    // Dump and die to see the paginated results
    return $paginatedRentals;
    //   return  view('Rentals.afterSearch.rentals', compact('paginatedRentals'));
  }
}
