<?php

namespace App\Http\Controllers;

use App\Models\HotelFacility;
use App\Models\FeatureHotel;
use Illuminate\Support\Facades\Http;
use App\Models\Hotel;
use App\Models\HotelDestination;
use DateTime;
use Illuminate\Support\Facades\Session;

use Illuminate\Http\Request;

class HotelController extends Controller
{
    public function listHotels()
    {
        // $page = $request->input('page');
        // $perPage = 15;
        $modifiedHotelList = [];

        $featureHotels = FeatureHotel::get();

        foreach ($featureHotels as $featureHotel) {
            $hotel = Hotel::select('name', 'city', 'address', 'image', 'images', 'rating', 'rooms')
                ->where('id', $featureHotel->hotel_id)
                ->first();

            if ($hotel) {
                if ($hotel->city == null) {
                    $hotel->city = $hotel->address;
                }

                if ($hotel->images != null) {
                    if (preg_match('/"path":\s*"(.*?)"/', $hotel->images, $matches)) {
                        $pathValue = $matches[1];
                        $hotel->image = 'http://photos.hotelbeds.com/giata/' . $pathValue;
                    }
                }

                $modifiedHotelList[] = $hotel;
            }
        }

        return $modifiedHotelList;
    }

    public function listHotelsHome()
    {
        $hotelList = Hotel::select('name', 'city', 'image', 'images', 'rating', 'rooms')->where('rating', '>', 4)->paginate(10);
        foreach ($hotelList as $list) {

            if ($list->images != null) {
                if (preg_match('/"path":\s*"(.*?)"/', $list->images, $matches)) {
                    $pathValue = $matches[1];
                    $list->image = 'http://photos.hotelbeds.com/giata/' . $pathValue;
                    // echo $pathValue; // Output: 01/013689/013689a_hb_r_001.jpg
                    //                } else {
                    //                    echo "Path value not found.";
                }
            }
        }
        // print_r($hotelList);
        // dd('hlo');
        return ($hotelList);
    }

    public function cacheHotel()
    {
        $hotelList = Hotel::select('id', 'name', 'city', 'state')
            ->get();
        $hotelList2 = Hotel::select('id', 'city')
            ->get();

        $combinedHotelData = [];

        // Extract unique cities from $hotelList2
        $existingCities = [];
        foreach ($hotelList2 as $list2) {
            $cityLowercase = strtolower(trim($list2->city));
            if (!in_array($cityLowercase, $existingCities)) {
                $data = [
                    'id' => $list2->id,
                    'data' => $list2->city,
                    'type' => 'city'
                ];
                $combinedHotelData[] = $data;
                $existingCities[] = $cityLowercase;
            }
        }

        // Combine hotel and city data
        foreach ($hotelList as $list) {
            $data = [
                'id' => $list->id,
                'data' => $list->name . ',' . $list->city . ',' . $list->state,
                'type' => "hotel"
            ];

            $combinedHotelData[] = $data;
        }

        return ($combinedHotelData);
    }
    public function citesList()
    {
        $hotelList = Hotel::select('city', 'country')->distinct()->get();
        return ($hotelList);
    }

    public function searchHotel(Request $request)
    {
        $search = $request->query('search');
        Session::forget('search_data');

        $hotelList = Hotel::select('name', 'city', 'state')
            ->where('name', 'like', "%$search%")
            ->get();

        // Fetch hotels matching the search term in city
        $hotelList2 = Hotel::select('city')
            ->where('city', 'like', "%$search%")
            ->get();

        $combinedHotelData = [];

        // Extract unique cities from $hotelList2
        $existingCities = [];
        foreach ($hotelList2 as $list2) {
            $cityLowercase = strtolower(trim($list2->city));
            if (!in_array($cityLowercase, $existingCities)) {
                $data = [

                    'data' => $list2->city,
                    'type' => 'city'
                ];
                $combinedHotelData[] = $data;
                $existingCities[] = $cityLowercase;
            }
        }

        // Combine hotel and city data
        foreach ($hotelList as $list) {
            $data = [

                'data' => $list->name . ',' . $list->city . ',' . $list->state,
                'type' => "hotel"
            ];
            $combinedHotelData[] = $data;
        }

        // Store combined data in session
        Session::put('search_data', ['data' => $combinedHotelData]);

        return response()->json(['data' => $combinedHotelData]);

        // return ($combinedHotelData);
    }

    public function searchHotelOne(Request $request)
    {
        $search = $request->query('search');
        Session::forget('search_data');

        $hotelList = Hotel::select('id', 'name', 'city', 'state')
            ->where('name', 'like', "%$search%")
            ->take(10)
            ->get();
        // dd($hotelList);
        // Fetch hotels matching the search term in city
        $hotelList2 = HotelDestination::select('id', 'name')
            ->where('name', 'like', "%$search%")
            ->take(10)
            ->get();

        $combinedHotelData = [];

        // Extract unique cities from $hotelList2

        foreach ($hotelList2 as $list2) {
            $data = [
                'id' => $list2->id,
                'data' => $list2->name,
                'type' => 'city'
            ];
            $combinedHotelData[] = $data;
        }

        // Combine hotel and city data
        foreach ($hotelList as $list) {
            $data = [
                'id' => $list->id,
                'data' => $list->name . ',' . $list->city . ',' . $list->state,
                'type' => "hotel"
            ];
            $combinedHotelData[] = $data;
        }
        return response()->json(['data' => $combinedHotelData]);

        // return ($combinedHotelData);
    }
    public function getLocations(Request $request)
    {
        $term = $request->input('city');

        $locations = Hotel::where('city', 'like', '%' . $term . '%')
            ->distinct()
            ->union(
                Hotel::where('name', 'like', '%' . $term . '%')
                    ->distinct()
            )
            ->get();

        return response()->json(['data' => $locations]);
    }

    public function searchHotels(Request $request, $city, $checkin, $checkout, $rooms, $adults, $childs, $nationality)
    {
        $searchTerm = str_replace('-', ' ', $city);
        $age1 = $_GET['age1']; // Contains '2'
        $age2 = $_GET['age2']; // Contains '3'
        dd($age1);
        $meta = Hotel::select(
            'id',
            'name',
            'city',
            'country',
            'image',
            'rating',
            'address',
        )
            ->where('name',  $searchTerm)
            ->orWhere('city', $searchTerm)
            ->get();

        return response()->json(['data' => $meta]);
    }
    public function hotelSearch(Request $request)
    {
        $search =  $request->query('search');
        $hotel_data = Hotel::where('name', $search)->first();
        // dd($search);
        $explict_data = explode(",", $search);

        if (count($explict_data) > 2) {
            // Combine all elements except the last one
            $destination = null;
            $name = implode(",", array_slice($explict_data, 0, -1));
            // dd($name);
        } elseif (count($explict_data) == 2) {

            $destination = null;
            $name = $explict_data[0];
            // dd($name);
        } else {
            $destination = $search;
            $name = null;
        }
        $checkin =  $request->query('checkin');
        $checkout =  $request->query('checkout');
        $adults =  $request->query('adults');
        $child =  $request->query('child');
        $age =  $request->query('age');
        // dd($child);
        $checkin_date = null;
        if ($checkin != null) {
           
            $checkin_date = $checkin;
        }
        $checkout_date = null;
        if ($checkout != null) {
            $checkout_date = $checkout;
        }

        $age_array = [];
        for ($i = 1; $i <= intval($child); $i++) {
            $age = $request->query('age' . (string)$i);
            $age_array[] = intval($age);
            // dd();
        }
        //    dd('hl');
        // if ($age != null) {
        //     $age_array = array_values($age);
        // }
        // dd( array_values($age)); //
        ini_set('max_execution_time', 280);

        $url = '/hotels/search';
        $Params = [
            "destination" => $destination,
            "name" =>  $name,
            "checkin" => $checkin_date,
            "checkout" => $checkout_date,
            "rooms" => [
                [
                    "adults" => intval($adults),
                    "children" => intval($child),
                    "childage" => $age_array
                ]
            ]
        ];
        // dd($Params);
        $response = Http::petapi()->post($url, $Params);
        // echo ($response);
        // dd($response);
        if ($response->failed()) {
            $countries_controller = new CountryController();
            $get_countries = $countries_controller->getCountries();
            // Prepare the data to be passed to the view
            $data = [
                "countries" => $get_countries
            ];
            return $data;
        } else {
            $datas = $response->json();
            // dd($datas);
            if ($datas['total'] == 0) {
                $countries_controller = new CountryController();
                $get_countries = $countries_controller->getCountries();
                // Prepare the data to be passed to the view
                $data = [
                    "countries" => $get_countries
                ];
                return $data;
            } else if ($datas['total'] == 1) {
                $hotel_list = [];
                $hotelCodes = [];
                // dd($datas['hotels']);
                foreach ($datas['hotels'] as $item) {
                    $hotelCodes[] = $item['code'];
                }

                // Fetch hotel images and ratings in bulk
                $hotels = Hotel::where('code', $hotelCodes)->select(['id', 'code', 'image', 'images', 'url', 'rating', 'phones', 'facilities', 'description', 'comment'])->first();
                // dd($hotels);
                if ($hotels == null) {
                    Session::put('hotel_not_available', 'No Data Available');
                    $hotel_list[] = null;
                    return $hotel_list;
                } else {
                    // Map hotel data for faster lookup
                    $hotelData = [];

                    $hotelData[$hotels['code']] = ['id' => $hotels['id'], 'image' => $hotels['image'], 'images' => $hotels['images'], 'rating' => $hotels['rating'], 'phones' => $hotels['phones'], 'description' => $hotels['description']];
                    foreach ($datas['hotels'] as &$item) {
                        if (isset($hotelData[$item['code']])) {
                            $separatedData = [];
                            if ($hotels['facilities'] != null) {
                                foreach (json_decode($hotels['facilities']) as $items) {
                                    $groupCode = $items->facilityGroupCode;
                                    $facilities_name = HotelFacility::where('code', $groupCode)->select(['name'])->first();
                                    // dd($facilities_name['name']);
                                    if (!isset($separatedData[$facilities_name['name']])) {
                                        $separatedData[$facilities_name['name']] = [];
                                    }
                                    $separatedData[$facilities_name['name']][] = $items;
                                }
                            }
                            // dd(   $separatedData );
                            // $item['image'] = $hotelData[$item['code']]['image'];
                            // $item['rating'] = $hotelData[$item['code']]['rating'];
                            if ($hotelData[$item['code']]['images'] != null) {

                                $hotelData[$item['code']]['image'] = json_decode($hotels['images']);
                                // echo $pathValue; // Output: 01/013689/013689a_hb_r_001.jpg

                            }
                            foreach ($hotelData[$item['code']]['image'] as &$image) {
                                // Check if the image has a "roomCode" attribute
                                if (isset($image->roomCode)) {
                                    // Loop through each room
                                    foreach ($item['rooms'] as &$room) { // Note the use of "&" to reference the original array
                                        // Compare the "roomCode" with the "code" of each room
                                        if ($image->roomCode === $room['code']) {
                                            // If a match is found, add the "path" to the room data
                                            $room['image_path'] = 'https://photos.hotelbeds.com/giata/' . $image->path;
                                            // Break out of the loop since we found the matching room
                                            break;
                                        }
                                    }
                                }
                                unset($room); // Unset the reference to prevent any unintended modifications outside the loop
                            }
                            foreach ($item['rooms'] as &$room) {
                                foreach ($room['rates'] as &$rate) {
                                    foreach ($rate['cancellationPolicies'] as &$policy) {
                                        // Subtract 1 day from the "from" date
                                        $fromDate = new DateTime($policy['from']);
                                        $fromDate->modify('-1 day');
                                        $policy['from'] = $fromDate->format('Y-m-d\TH:i:sP');
                                    }
                                }
                            }
                            $hotel_list[] = [
                                "id" => $hotels["id"],
                                "code" => $hotels['code'],
                                "name" => $item['name'],
                                "hotel_image" => 'https://photos.hotelbeds.com/giata/' . $hotelData[$item['code']]['image'][0]->path,
                                "facilities" => $separatedData,
                                "rating" => $hotelData[$item['code']]['rating'],
                                "description" => $hotelData[$item['code']]['description'],
                                "city" => $item['destinationName'],
                                "rooms" => $item['rooms'],
                                "phones" => $hotels['phones'],
                                "comments" => $hotels['comment'],
                                "url" => $hotels['url']
                            ];
                        }
                    }
                    // dd($hotel_list);
                    $data = [
                        'hotel_list' => $hotel_list,
                        'search' => $search
                    ];
                    Session::put('hotel_details',$hotel_list);
                    // dd( Session::get('hotel_details'));
                    return $data;
                }
            } else {
                $hotel_list = [];
                $hotelCodes = [];
                // dd($datas['hotels'][0]);
                foreach ($datas['hotels'] as $item) {
                    $hotelCodes[] = $item['code'];
                }

                // Fetch hotel images and ratings in bulk
                $hotels = Hotel::whereIn('code', $hotelCodes)->get(['id', 'code', 'image', 'images', 'rating', 'description']);

                // Map hotel data for faster lookup
                $hotelData = [];
                foreach ($hotels as $hotel) {
                    $hotelData[$hotel['code']] = ['id' => $hotel['id'], 'image' => $hotel['image'], 'images' => $hotel['images'], 'rating' => $hotel['rating'], 'description' => $hotel['description']];
                }
                // Assign image and rating to each hotel
                foreach ($datas['hotels'] as &$item) {
                    if (isset($hotelData[$item['code']])) {
                        // $item['image'] = $hotelData[$item['code']]['image'];
                        // $item['rating'] = $hotelData[$item['code']]['rating'];
                        if ($hotelData[$item['code']]['images'] != null) {
                            if (preg_match('/"path":\s*"(.*?)"/', $hotelData[$item['code']]['images'], $matches)) {
                                $pathValue = $matches[1];
                                $hotelData[$item['code']]['image'] = 'http://photos.hotelbeds.com/giata/' . $pathValue;
                                // echo $pathValue; // Output: 01/013689/013689a_hb_r_001.jpg
                            }
                        }
                        $hotel_list[] = [
                            "id" => $hotelData[$item['code']]['id'],
                            "name" => $item['name'],
                            "image" => $hotelData[$item['code']]['image'],
                            "rating" => $hotelData[$item['code']]['rating'],
                            "description" => $hotelData[$item['code']]['description'],
                            "city" => $item['destinationName'],
                            "minRate" => number_format($item['minRate'], 2),
                            "currency" => $item['currency']
                            // "amount"=>
                        ];
                    } else {
                        $hotel_list[] = [
                            "name" => $item['name'],
                            "image" => null,
                            "rating" => (int)filter_var($item['categoryName'], FILTER_SANITIZE_NUMBER_INT),
                            "description" => null,
                            "city" => $item['destinationName'],
                            "minRate" => number_format($item['minRate'], 2),
                            "currency" => $item['currency']
                        ];
                    }
                }
                $countries_controller = new CountryController();
                $get_countries = $countries_controller->getCountries();
                // Prepare the data to be passed to the view
                $data = [
                    "hotel_lists" => $hotel_list,
                    "countries" => $get_countries
                ];
                return $data;
            }
        }

        // dd($datas['hotels']);

    }

    public function hotel()
    {
        Session::forget('search_data');
        $hotelController = new HotelController();
        // $cache_hotel=$hotelController->cacheHotel();
        // Queue::push($cache_hotel);
        $hotel_list = $hotelController->listHotels();
        $countries_controller = new CountryController();
        $get_countries = $countries_controller->getCountries();
        // Prepare the data to be passed to the view
        $data = [
            "hotel_lists" => $hotel_list,
            "countries" => $get_countries,
            // "cache_hotel"=>$cache_hotel
        ];
        return response()->json($data);
        // return view('Hotels.hotels', compact('data'));
    }


    // public function hotelDetails()
    // {
    //     return view('Hotels.details');
    // }

    public function selectRoom(Request $request)
    {


        $rate_keys = $request->rate_keys;
        $selectedRooms = $request->selectedRooms;
        $hotel_list = [];
        $rooms_data = [];
        $hotel_details =$request->hotel_details;
        // return $hotel_details;
        foreach ($rate_keys as $rate_key) {
                foreach ($hotel_details['rooms'] as $room) {
                    foreach ($room['rates'] as $rate) {
                        if ($rate['rateKey'] == $rate_key) {
                            $room['rates'] = $rate;

                            $rooms_data[] = $room;
                        }
                    }
                }
            $hotel_list = [
                "code" => $hotel_details['code'],
                "name" => $hotel_details['name'],
                "image" => $hotel_details['hotel_image'],
                "rating" => $hotel_details['rating'],
                "rooms" => $rooms_data
            ];
        }
        $data = [
            'selected_Room' => $selectedRooms,
            'hotel_reservation' => $hotel_list
        ];
        return $data;
    }

    public function viewMore(Request $request)
    {
        $name = $request->query('name');
        $hotel = Hotel::where('name', $name)->select(['name', 'image', 'images', 'phone', 'phones', 'facilities', 'url', 'rating', 'city', 'address'])->first();
        // dd($hotel);
        $countries_controller = new CountryController();
        $get_countries = $countries_controller->getCountries();

        if ($hotel->city == null) {
            $hotel->city = $hotel->address;
        }
        if ($hotel->images != null) {
            if (preg_match('/"path":\s*"(.*?)"/', $hotel->images, $matches)) {
                $pathValue = $matches[1];
                $hotel->image = 'http://photos.hotelbeds.com/giata/' . $pathValue;
                // echo $pathValue; // Output: 01/013689/013689a_hb_r_001.jpg
            } else {
                echo "Path value not found.";
            }
        }
        $separatedData = [];
        if ($hotel['facilities'] != null) {
            // dd($hotel['facilities']);
            if (json_decode($hotel['facilities']) != null) {
                foreach (json_decode($hotel['facilities']) as $items) {
                    $groupCode = $items->facilityGroupCode;
                    $facilities_name = HotelFacility::where('code', $groupCode)->select(['name'])->first();
                    // dd($facilities_name['name']);
                    if (!isset($separatedData[$facilities_name['name']])) {
                        $separatedData[$facilities_name['name']] = [];
                    }
                    $separatedData[$facilities_name['name']][] = $items;
                }
            }
        }
        $data = [
            "hotel_datas" => [
                "name" => $hotel['name'],
                "image" => $hotel['image'],
                "phone" => $hotel['phone'],
                "phones" => $hotel['phones'],
                "facilities" => $separatedData,
                "url" => $hotel['url'],
                "rating" => $hotel['rating'],
                "city" => $hotel['city']

            ],
            "countries" => $get_countries
        ];
        // dd($data
        return $data;
    }
}
