<?php

namespace App\Http\Controllers;

use App\Models\FeatureTour;
use App\Models\Tour\Destination;
use App\Models\Tour\Attraction;
use App\Models\Tour\Tag;
use App\Models\Tour\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TourController extends Controller
{
    public function getAttraction(Request $request)
    {
        $id = $request->query('id');
        $destinations = Destination::where('id', $id)->with(['attraction'])->get();

        return $destinations;
    }
    public function featureDestination(Request $request)
    {
        $features = FeatureTour::get();
        $destinationData = [];
        foreach ($features as $feature) {
            $destinations = Destination::with(['attractions'])->where('id', $feature->tour_id)->get();

            $destinationData[] = $destinations->map(function ($destination) {
                $attraction = $destination->attractions->first();
                return [
                    'id' => $destination->id,
                    'parent_id' => $destination->parent_id,
                    'lookup_id' => $destination->lookup_id,
                    'type' => $destination->type,
                    'name' => $destination->name,
                    'latitude' => $destination->latitude,
                    'longitude' => $destination->longitude,
                    'timezone' => $destination->timezone,
                    'iata_code' => $destination->iata_code,
                    'currency_code' => $destination->currency_code,
                    'first_attraction_thumbnail_url' => $attraction ? $attraction->thumbnail_url : null,
                    'first_attraction_thumbnail_hi_url' => $attraction ? $attraction->thumbnail_hi_url : null,
                ];
            })->toArray()[0];
        }
        return $destinationData;
    }

    public function searchListTour(Request $request)
    {
        $search = $request->query('search');

        $results = Destination::with(['attractions'])
            ->orWhere('name', 'LIKE', '%' . $search . '%')
            ->orWhereHas('attractions', function ($query) use ($search) {
                $query->where('title', 'LIKE', '%' . $search . '%');
            })
            ->take(5)
            ->distinct()
            ->get();
        //  dd( $results->toArray());
        // // Process results to add only the matched attraction names
        $processedResults = [];

        foreach ($results as $result) {
            // Check if the destination name matches the search term
            if (stripos($result->name, $search) !== false) {
                $processedResults[] = [
                    'type' => 'destination',
                    'name' => $result->name,
                ];
            }

            // Check if any attraction name matches the search term
            foreach ($result->attractions  as $attraction) {
                if (stripos($attraction->title, $search) !== false) {
                    $processedResults[] = [
                        'type' => 'attraction',
                        'name' => $attraction->title,
                    ];
                }
            }
        }

        return response()->json($processedResults);
    }

    public function afterSearchTour(Request $request)
    {
        $query = Product::with([
            'destinations:id,name',
            'destinations.attractions:id,destination_id,title',
            'schedules:product_code,timeofday,duration,from_price',
            'tags:id,name',
        ]);
        if ($request->input('is_sorting')) {
            if ($request->input('sort') == "traveler_rating") {
                $query->orderBy('reviews->combinedAverageRating', 'desc');
            } else if ($request->input('sort') == "priceLtH") {
                $query->whereHas('schedules')
                    ->orderByRaw('(SELECT CAST(from_price AS UNSIGNED) FROM viator_schedules WHERE viator_schedules.product_code = viator_products.code LIMIT 1) ASC');
            } else {
                $query->whereHas('schedules')->orderByRaw('(SELECT CAST(from_price AS UNSIGNED) FROM viator_schedules WHERE viator_schedules.product_code = viator_products.code LIMIT 1) DESC');;
            }
        } else {
            $query->orderBy('reviews->combinedAverageRating', 'desc');
        }
        if ($request->input('is_clicked')) {
            if ($destination = $request->input('destination')) {
                //$query->whereHas('destinations', fn($q) => $q->where('name', 'LIKE', '%' . $destination . '%'));
                $destIds = Destination::select('id')->where('name', 'like', "%$destination%")
                    ->pluck('id')
                    ->toArray();
                $query->whereHas('destinations', fn($q) => $q->whereIn('id', $destIds));
            }

            // Filter by attraction title
            else {
                $attraction = $request->input('attraction');
                //$query->whereHas('destinations.attractions', fn($q) => $q->where('title', 'LIKE', '%' . $attraction . '%'));

                $attrIds = Attraction::select('id')->where('title', 'like', "%$attraction%")
                    ->pluck('id')
                    ->toArray();
                $query->whereHas('attractions', fn($q) => $q->whereIn('id', $attrIds));
            }
        }
        if ($request->input('is_filter')) {
            // Filter by rating
            if ($rating = $request->input('rating')) {
                $query->where('reviews->combinedAverageRating', '>=', $rating);
            }

            // Filter by duration range in schedules
            elseif ($request->filled(['start_duration', 'end_duration'])) {
                $query->whereHas('schedules', fn($q) => $q->whereBetween('duration', [$request->start_duration, $request->end_duration]));
            }

            // Filter by time of day in schedules
            elseif ($timeOfDay = $request->input('time_of_day')) {
                $query->whereHas('schedules', fn($q) => $q->whereRaw("timeofday & $timeOfDay > 0"));
            }

            // Search by title or destination name
            

            // Filter by tags
            elseif ($tags = $request->input('tags')) {
                $query->whereHas('tags', fn($q) => $q->whereIn('name', $tags));
            }

            // Filter by price range in schedules
            elseif ($request->filled(['min_price', 'max_price'])) {
                $minPrice = (int) $request->min_price;
                $maxPrice = (int) $request->max_price;

                $query->whereHas('schedules', fn($q) => $q->whereBetween(DB::raw('CAST(from_price AS UNSIGNED)'), [$minPrice, $maxPrice]));
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('destinations', fn($q) => $q->where('name', 'LIKE', '%' . $search . '%'));
            });
        }
        // Execute the query with dynamic pagination
        $perPage = $request->input('per_page', 10); // Default to 10 if not specified
        $toursData = $query->select(['id', 'code', 'booking_conf_setting', 'cancellation_policy', 'description', 'title', 'images', 'reviews'])->paginate($perPage);

        // Return the response with paginated tour data
        return [
            'tours' => $toursData,
        ];
    }
    public function getTourDetails($id)
    {
        $tour = Product::with(['destinations', 'questions', 'tags'])->find($id);

        return $tour;
    }

    public function checkAvailability(Request $request, $tour_id)
    {

        $travelDate = $request->travelDate;
        $currency = $request->currency;
        $paxMix = $request->paxMix;
        $queryParams = [
            "travelDate" => $travelDate,
            "currency" => $currency,
            "paxMix" => $paxMix
        ];
        $url = "/tours/" . $tour_id . '/check';
        $response = Http::petapi()->post($url, $queryParams);

        if ($response->failed()) {
            return response()->json([
                'error' => 'HTTP request failed',
                'status' => $response->status(),
            ], $response->status());
        }

        $data = $response->json();
        return $data;
    }

    public function getTags()
    {
        $tags = Tag::select('name')->get();
        $tags_data = $tags->toArray();
        return $tags_data;
    }

    public function holdTours(Request $request)
    {
        $tour_id = $request->tour_id;
        $productOptionCode = $request->productOptionCode;
        $travelDate = $request->travelDate;
        $startTime = $request->startTime;
        $currency = $request->currency;
        $paxMix = $request->paxMix;
        $queryParams = [
            "productCode" => $tour_id,
            "productOptionCode" => $productOptionCode,
            "startTime" => $startTime,
            "travelDate" => $travelDate,
            "currency" => $currency,
            "paxMix" => $paxMix
        ];
        // dd($queryParams);
        $url = "/tours/" . $tour_id . '/hold';
        $response = Http::petapi()->post($url, $queryParams);

        if ($response->failed()) {
            return response()->json([
                'error' => 'HTTP request failed',
                'status' => $response->status(),
            ], $response->status());
        }

        $data = $response->json();
        return $data;
    }
    public function locations(Request $request)
    {
        // $queryParams = [
        //     "locations"=>[
        //        "LOC-o0AXGEKPN4wJ9sIG0RAn5I8MezjwgQw2Mbnbb8ylQVFtfpS9Gf6EDiMXe3hrj7fp",
        //        "LOC-9Z9s24NnROkLKWSBKzWlv+htKbWIyIwovKhU1lzO06BSw/1pN2fAKf9HIuHyB2q4NMxZcdOtTPDlTvtRoHg3MBCLwzEM/qgBQ8VFf/Imn8rH42ZBuNEmnv7hPG+4bamCqliAVBQwwd8fEz4OR1a41y2V64MqT2Zmy1AxE+5fh04IdyTGyrEtyz0/mIq4fqkk+k2AJ+kP6Z8JE12wA2TCiQ=="
        //     ]

        // ];
        $queryParams = $request->locations;
        $url = "/tours/locations";
        $response = Http::petapi()->post($url, $queryParams);
        $data = $response->json();
        if ($response->failed()) {
            return response()->json([
                'error' => 'HTTP request failed',
                'status' => $response->status(),
            ], $response->status());
        }
        // https://places.googleapis.com/v1/places/ChIJj61dQgK6j4AR4GeTYWZsKWw?fields=id,displayName&key=API_KEY
        //    googleplaceapi
        $data = $response->json();
        if ($data != null) {
            $locations = $data['locations'];
            $retval = [];
            foreach ($locations as $location) {
                $provider = $location['provider'];
                if ($provider == "GOOGLE") {
                    $providerReference = $location['providerReference'];
                    $url = "/" . $providerReference;
                    $response = Http::googleplaceapi()->get($url);
                    $retval[] = $response->json();
                    //dd($data);
                } else {
                    $retval[] = $location;
                }
                // dd($location['provider']);

            }
        }        //dd($data['locations']);
        return $retval;
    }
}
