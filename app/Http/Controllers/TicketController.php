<?php

namespace App\Http\Controllers;

use App\Models\FeatureTicket;
use App\Models\TicketProduction;
use App\Models\TicketVenue;
use App\Models\TicketCategory;
use App\Models\TicketPerformer;
use App\Models\TicketProductionPerformer;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

class TicketController extends Controller
{
    public function toEvents(Collection $ticketProductions)
    {
        // Iterate through each production
        $events = [];
        foreach ($ticketProductions as $ticketProduction) {
            // Skip blank venue
            if ($ticketProduction->venue == null)
                continue;

            $events[] = [
                "production_id" => $ticketProduction->production_id,
                "name" => $ticketProduction->name,
                "occurred_at" => $ticketProduction->occurred_at,
                "venue_name" => $ticketProduction->venue->name ?? '',
                "venue_address" => $ticketProduction->venue->address ?? '',
                "venue_city" => $ticketProduction->venue->city ?? '',
                "venue_state" => $ticketProduction->venue->state ?? '',
                "venue_country_code" => $ticketProduction->venue->country_code ?? '',
                "popularity_score" => $ticketProduction->popularity_score // Add this for sorting
            ];
        }
        return $events;
    }
    public function listTicket()
    {
        $feature_tickets = FeatureTicket::with('production')->get();

        $events = [];
        foreach ($feature_tickets as $feature_ticket) {
            $ticketProduction = $feature_ticket->production;
            if ($ticketProduction) {
                if ($ticketProduction->venue != null) {
                    $events[] = [
                        "production_id" => $ticketProduction->production_id,
                        "name" => $ticketProduction->name,
                        "occurred_at" => $ticketProduction->occurred_at,
                        "venue_name" => $ticketProduction->venue->name,
                        "venue_address" => $ticketProduction->venue->address,
                        "venue_city" => $ticketProduction->venue->city,
                        "venue_state" => $ticketProduction->venue->state,
                        "venue_country_code" => $ticketProduction->venue->country_code,
                        "popularity_score" => $ticketProduction->popularity_score
                    ];
                } else {
                    $events[] = [
                        "production_id" => $ticketProduction->production_id,
                        "name" => $ticketProduction->name,
                        "occurred_at" => $ticketProduction->occurred_at,
                        "venue_name" => null,
                        "venue_address" => null,
                        "venue_city" => null,
                        "venue_state" => null,
                        "venue_country_code" => null,
                        "popularity_score" => $ticketProduction->popularity_score
                    ];
                }
            }
        }
        usort($events, function ($a, $b) {
            return $b['popularity_score'] <=> $a['popularity_score']; // Descending order
        });
        // dd($events);
        return $events;
    }

    // after search ticket
    public function searchTicket(Request $request)
    {
        $search = $request->query('search');

        $url = '/tickets';
        $queryParams = [
            "productionId" => $search
        ];

        // Check if data is cached
        $cacheKey = 'tickets_' . $search;
        $details = Cache::remember($cacheKey, now()->addSecond(1), function () use ($url, $queryParams, $search) {
            $response = Http::petapi()->get($url, $queryParams);

            if ($response->failed()) {
                return [];
            }
            $json_data = $response->json();
            // return $json_data;
            $ticketProductions = TicketProduction::with(['venue', 'category'])
                ->select(['production_id', 'name', 'venue_id', 'occurred_at', 'popularity_score', 'configuration_id', 'category_id'])
                ->where('production_id', $search)
                ->first();
            if ($ticketProductions == null) {
                $ticketProductions = new TicketProduction;
            }

            // dd($ticketProductions->toArray());
            if (!empty($json_data['data'])) {
                $seating_categories = array_keys($json_data['meta']['categories'] ?? []);
                return collect($json_data['data'])->map(function ($data) use ($seating_categories, $ticketProductions) {
                    $tevo = "";
                    if (isset($data['tevo_section_name'])) {
                        $tevo = $data['tevo_section_name'];
                    }
                    $date = new DateTime($data['event']['datetime']);
                    $formatted_date = $date->format('D F j, Y h:i A');
                    return [
                        'ticket_id' => $data['id'],
                        'production_id' => $data['event']['id'],
                        'event_name' => $ticketProductions['name'],
                        'ticket_split_type' => $data['ticket']['split_type'],
                        'map_url' => $data['event']['map_url'],
                        'tevo_section_name' => $tevo,
                        'seating_categories' => $seating_categories,
                        'date_time' => $formatted_date,
                        "configuration_id" => $ticketProductions->configuration_id,
                        "venue_id" => $ticketProductions->venue_id,
                        'location' => $data['event']['venue']['name'] . "," . $data['event']['venue']['city'] . "," . $data['event']['venue']['country_code'],
                        'category' => $data['seat_details']['category'],
                        "section" => $data['seat_details']['section'],
                        "row" => $data['seat_details']['row'],
                        "first_seat" => $data['seat_details']['first_seat'],
                        "quantity" => $data['number_of_tickets_for_sale']['quantity_available'],
                        "currency" => $data['proceed_price']['currency'],
                        "amount" =>  number_format($data['proceed_price']['amount'], 2),
                        "type" => 'tix',
                        "ticket_type" => '',
                        "in_hand"=>$data['delivery']['hand_delivered'],
                        "in_hand_on"=>$data['delivery']['shipped_date_or_date_in_hand']
                    ];
                })->all();
            } else if (!empty($json_data['ticket_groups'])) {
                $ticket_groups = $json_data['ticket_groups'] ?? [];
                return collect($ticket_groups)->map(function ($group) use ($ticketProductions) {
                    $date = new DateTime($ticketProductions->occurred_at);
                    $formatted_date = $date->format('D F j, Y h:i A');

                    return [
                        'ticket_id' => $group['id'],
                        'production_id' => $ticketProductions->production_id, // Mapping office ID as production ID
                        'event_name' => $ticketProductions->name, // Assuming event name from office name
                        'ticket_split_type' => implode(", ", $group['splits']),
                        'map_url' => "https://maps.ticketevolution.com/" . $ticketProductions->venue->venue_id . "/" . $ticketProductions->configuration_id . "/map.svg", // Not available in the second format
                        'seating_categories' => [], // Not available
                        'tevo_section_name' => $group['tevo_section_name'],
                        'date_time' => $formatted_date, // Use current date for demo
                        'location' => $ticketProductions->venue->name, // Placeholder for venue info
                        'category' => $ticketProductions->category->name, // Not available in this format
                        "section" => $group['section'] ?? '',
                        "configuration_id" => $ticketProductions->configuration_id,
                        "venue_id" => $ticketProductions->venue_id,
                        "row" => $group['row'] ?? '',
                        "first_seat" => $group['seat_numbers'] ?? '',
                        "quantity" => $group['available_quantity'],
                        "currency" => 'USD', // Assuming default currency
                        "amount" =>  number_format($group['retail_price'], 2) ?? 0,
                        "type" => 'tevo',
                        "ticket_type" => $group['format'],
                        "service_fee"=>$group['service_fee'],
                        "in_hand"=>$group['in_hand'],
                        "in_hand_on"=>$group['in_hand_on']
                    ];
                })->all();
            }
        });

        if ($details && count($details) > 0) {
            return $details;
        } else {
            $data = ["status" => false, "message" => "No Ticket Available"];

            $durationInSeconds = 8;
            $expirationTime = Carbon::now()->addSeconds($durationInSeconds);

            // Data to store in the session
            $data = [
                'message' => 'No Ticket Available',
                'expiration_time' => $expirationTime,
            ];

            // Store the data in the session
            return $data;
        }
    }

    //filter ticket based on category and no_of_tickets
    public function filterTicket(Request $request)
    {
        $no_of_ticket = $request->query('no_of_ticket');
        $production_id = $request->query('production_id');
        $category = $request->query('category');

        $queryParams = [
            "productionId" => $production_id
        ];
        $response = Http::petapi()->get('/tickets', $queryParams);

        if ($response->failed()) {
            echo 'HTTP request failed: ' . $response->status();
            return; // Exit the function if the request fails.
        }

        $datas = $response->json();
        $seating_categories = array_keys($datas['meta']['categories'] ?? []);

        $details = [];
        if (!empty($datas['data'])) {

            foreach ($datas['data'] as $data) {
                $date = new DateTime($data['event']['datetime']);
                $formatted_date = $date->format('D F j, Y h:i A');
                $common_data = [
                    'ticket_id' => $data['id'],
                    'production_id' => $data['event']['id'],
                    'event_name' => $data['event']['name'],
                    'map_url' => $data['event']['map_url'],
                    'seating_categories' => $seating_categories,
                    'date_time' => $formatted_date,
                    'location' => $data['event']['venue']['name'] . "," . $data['event']['venue']['city'] . "," . $data['event']['venue']['country_code'],
                ];

                // Check ticket quantity and category
                if ($no_of_ticket != null && $category != null) {
                    if ($no_of_ticket <= $data['number_of_tickets_for_sale']['quantity_available'] && $category == $data['seat_details']['category']) {
                        $common_data['category'] = $data['seat_details']['category'];
                        $common_data['ticket_split_type'] = $data['ticket']['split_type'];
                        $common_data["section"] = $data['seat_details']['section'];
                        $common_data["row"] = $data['seat_details']['row'];
                        $common_data["first_seat"] = $data['seat_details']['first_seat'];
                        $common_data["quantity"] = $data['number_of_tickets_for_sale']['quantity_available'];
                        $common_data["currency"] = $data['proceed_price']['currency'];
                        $common_data["amount"] = number_format($data['proceed_price']['amount'], 2);
                        // $details[] = $common_data;
                    } else {
                        $common_data;
                    }
                } elseif ($no_of_ticket != null) {
                    if ($no_of_ticket <= $data['number_of_tickets_for_sale']['quantity_available']) {
                        $common_data['category'] = $data['seat_details']['category'];
                        $common_data['ticket_split_type'] = $data['ticket']['split_type'];
                        $common_data["section"] = $data['seat_details']['section'];
                        $common_data["row"] = $data['seat_details']['row'];
                        $common_data["first_seat"] = $data['seat_details']['first_seat'];
                        $common_data["quantity"] = $data['number_of_tickets_for_sale']['quantity_available'];
                        $common_data["currency"] = $data['proceed_price']['currency'];
                        $common_data["amount"] = number_format($data['proceed_price']['amount'], 2);
                        // $details[] = $common_data;
                    } else {
                        $common_data;
                    }
                } elseif ($category != null) {
                    if ($category == $data['seat_details']['category']) {
                        $common_data['category'] = $data['seat_details']['category'];
                        $common_data['ticket_split_type'] = $data['ticket']['split_type'];
                        $common_data["section"] = $data['seat_details']['section'];
                        $common_data["row"] = $data['seat_details']['row'];
                        $common_data["first_seat"] = $data['seat_details']['first_seat'];
                        $common_data["quantity"] = $data['number_of_tickets_for_sale']['quantity_available'];
                        $common_data["currency"] = $data['proceed_price']['currency'];
                        $common_data["amount"] = number_format($data['proceed_price']['amount'], 2);
                        // $details[] = $common_data;
                    } else {
                        $common_data;
                    }
                } else {
                    // Add data to $details when no filtering is applied.
                    $common_data['category'] = $data['seat_details']['category'];
                    $common_data['ticket_split_type'] = $data['ticket']['split_type'];
                    $common_data["section"] = $data['seat_details']['section'];
                    $common_data["row"] = $data['seat_details']['row'];
                    $common_data["first_seat"] = $data['seat_details']['first_seat'];
                    $common_data["quantity"] = $data['number_of_tickets_for_sale']['quantity_available'];
                    $common_data["currency"] = $data['proceed_price']['currency'];
                    $common_data["amount"] =  number_format($data['proceed_price']['amount'], 2);
                    // $details[] = $common_data;
                }
                $details[] = $common_data;
            }
        }

        // If there's data in `ticket_groups`, handle that too.
        if (empty($details) && !empty($datas['ticket_groups'])) {
            foreach ($datas['ticket_groups'] as $data) {
                $ticketProductions = TicketProduction::with(['venue', 'category'])
                    ->where('production_id', $production_id)
                    ->first();

                if ($ticketProductions) {
                    $date = new DateTime($ticketProductions->occurred_at);
                    $formatted_date = $date->format('D F j, Y h:i A');
                    $common_data = [
                        'ticket_id' => $data['id'],
                        'production_id' => $ticketProductions->production_id,
                        'event_name' => $ticketProductions->name,
                        'map_url' => "https://maps.ticketevolution.com/" . $ticketProductions->venue->venue_id . "/" . $ticketProductions->configuration_id . "/map.svg",
                        'seating_categories' => [],
                        'date_time' => $formatted_date,
                        'location' => $ticketProductions->venue->name,
                    ];

                    if ($no_of_ticket != null && $no_of_ticket <= $data['available_quantity']) {
                        $common_data['category'] = $ticketProductions->category->name;
                        $common_data['ticket_split_type'] = implode(", ", $data['splits']);
                        $common_data["section"] = $data['section'];
                        $common_data["row"] = $data['row'];
                        $common_data["first_seat"] = $data['seat_numbers'];
                        $common_data["quantity"] = $data['available_quantity'];
                        $common_data["currency"] = 'USD';
                        $common_data["amount"] = number_format($data['retail_price'], 2);
                        $details[] = $common_data;
                    }
                }
            }
        }


        // Return the final details array to the view.
        return $details;
    }

    //BuyTicket
    public function buyTicket(Request $request)
    {
        $ticket_id = $request->query('ticket_id');
        $category = $request->query('category');

        $url = '/tickets';
        $queryParams = [
            "ticketId" => $ticket_id
        ];

        $response = Http::petapi()->get($url, $queryParams);
        if ($response->failed()) {
            echo 'HTTP request failed: ' . $response->status();
        } else {
            $datas = $response->json();
            $data = $datas['data'][0];
            $details = [];

            $combine_data = [
                'seller_name' => $data['seller_name'],
                'category' => $data['seat_details']['category'],
                "section" => $data['seat_details']['section'],
                "row" => $data['seat_details']['row'],
                "type" => $data['ticket']['type'],
                "first_seat" => $data['seat_details']['first_seat'],
                "quantity" => $data['number_of_tickets_for_sale']['quantity_available'],
                "currency" => $data['proceed_price']['currency'],
                "amount" => $data['proceed_price']['amount'],
            ];

            $details[] = $combine_data;
            return response()->json(['data' => $details]);
        }
    }

    //search performer,venues
    public function searchTicketPerformerVenues(Request $request)
    {
        $search = $request->query('search');
        $ticketPerformer = TicketPerformer::select(['performer_id', 'name', 'events_count'])
            ->where('name', 'like', "%$search%")
            ->where('events_count', '>', 0)
            ->orderBy('events_count', 'desc') // Sort by events_count in descending order
            ->take(100)
            ->get()
            ->toArray();

        $ticketVenue = TicketVenue::select(['venue_id', 'name', 'events_count'])
            ->where('name', 'like', "%$search%")
            ->where('events_count', '>', 0)
            ->orderBy('events_count', 'desc') // Sort by events_count in descending order
            ->take(100)
            ->get()
            ->toArray();
        $uniqueEvents = [];
        $uniqueEvents = [
            "performer" => $ticketPerformer,
            "venues" => $ticketVenue
        ];
        return response()->json(['data' => $uniqueEvents]);
    }

    //search cities
    public function searchTicketCities(Request $request)
    {
        $search = $request->query('search');
        $ticketVenue = TicketVenue::where('city', 'like', "%$search%")
            ->take(100)
            ->select('city')
            ->distinct()
            ->get()
            ->toArray();

        return response()->json(['data' => $ticketVenue]);
    }

    /**
     * Pick a Performer/Venue from the dropdown
     *
     */
    public function searchlistTicket(Request $request)
    {
        $search = $request->query('search');
        $ticketProductions = TicketProduction::with(['performers', 'venue'])->where(function ($builder) use ($search) {
            $builder->where('name', $search)
                ->orWhereHas('venue', function ($query) use ($search) {
                    $query->where('name', $search);
                })
                ->orWhereHas('performers', function ($query) use ($search) {
                    $query->where('name', $search);
                });
        });
        $ticketProductions = $ticketProductions->orderBy('occurred_at', 'asc')->take(100)->get();

        $events = $this->toEvents($ticketProductions);
        return $events;
    }

    //
    // click search to find the ticket based on start/end date, city, production/venue/performer name 
    //
    //
    public function searchTicketCitiesDates(Request $request)
    {
        $start_date = null;
        if ($request->query('start_date') != null) {
            $start_date = DateTime::createFromFormat('Y-m-d', $request->query('start_date'));
        }
        $end_date = null;
        if ($request->query('end_date') != null) {
            $end_date = DateTime::createFromFormat('Y-m-d', $request->query('end_date'));
        }

        $ticketProductions = TicketProduction::select(['production_id', 'name', 'venue_id', 'occurred_at']);
        if ($start_date) {
            $ticketProductions = $ticketProductions->whereDate('occurred_at', '>=', $start_date);
        }
        if ($end_date) {
            $ticketProductions = $ticketProductions->whereDate('occurred_at', '<=', $end_date);
        }

        $city = $request->query('city');
        if ($city) {
            $ticketProductions = $ticketProductions->whereHas('venue', function ($query) use ($city) {
                $query->where('city', $city);
            });
        }

        /* complex - or of name/performer/venu for the last */
        $search = $request->query('search');
        if ($search) {
            $ticketProductions = $ticketProductions->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('venue', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('performers', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    });
            });
        }
        $ticketProductions = $ticketProductions->orderBy('occurred_at', 'asc')->take(100)->get();

        $events = $this->toEvents($ticketProductions);
        return $events;
    }

    /**
     *  ??? same as  searchTicketCitiesDates ???
     *  Overly complex logic ....
     */
    public function searchFilter(Request $request)
    {
        $start_date = null;
        if ($request->query('start_date') != null) {


            $start_date = DateTime::createFromFormat('Y-m-d', $request->query('start_date'));
            // dd($date);
            // $checkin_date = $date->format('Y-m-d');
        }
        $end_date = null;
        if ($request->query('end_date') != null) {

            $end_date = DateTime::createFromFormat('Y-m-d', $request->query('end_date'));
        }
        $ticketProductions = TicketProduction::with('venue')
            ->select(['production_id', 'name', 'venue_id', 'occurred_at']);
        if ($start_date) {
            $ticketProductions = $ticketProductions->whereDate('occurred_at', '>=', $start_date);
        }
        if ($end_date) {
            $ticketProductions = $ticketProductions->whereDate('occurred_at', '<=', $end_date);
        }

        $city = $request->query('city');
        if ($city) {
            $ticketProductions = $ticketProductions->whereHas('venue', function ($query) use ($city) {
                $query->where('city', $city);
            });
        }

        $search = $request->query('search');
        if ($search) {
            $ticketProductions = $ticketProductions->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('venue', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('performers', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    });
            });
        }
        $ticketProductions = $ticketProductions->take(100)->get();

        $events = [];
        foreach ($ticketProductions as $ticketProduction) {
            $events[] = [
                "production_id" => $ticketProduction->production_id,
                "name" => $ticketProduction->name,
                "occurred_at" => $ticketProduction->occurred_at,
                "venue_name" => $ticketProduction->venue->name,
                "venue_address" => $ticketProduction->venue->address,
                "venue_city" => $ticketProduction->venue->city,
                "venue_country_code" => $ticketProduction->venue->country_code
            ];
        }
        usort($events, function ($a, $b) {
            return strtotime($a['occurred_at']) - strtotime($b['occurred_at']);
        });
        return $events;
    }

    public function holdTicket($ticketId, $quantity)
    {
        $data = [];
        $url_second = '/tickets/' . $ticketId . '/hold';
        $queryParams_second = [
            "ticket_id" => $ticketId,
            "quantity" => intval($quantity)
        ];
        $response = Http::petapi()->post($url_second, $queryParams_second);
        if ($response->failed()) {
        } else {
            $datas = $response->json();
            $data[] = [
                "ticketId" => $ticketId,
                "hold_id" => $datas['hold_id'],
                "quantity" => $quantity,
            ];
            return $datas;
        }
    }

    public function unHoldTicket(Request $request)
    {
        $hold_id = $request->query('hold_id');
        $ticket_id = $request->query('ticket_id');
        $quantity = $request->query('quantity');

        $url_second = '/tickets/' . $ticket_id . '/hold';
        $queryParams_second = [
            "ticket_id" => $ticket_id,
            "quantity" => intval($quantity),
            "holdId" => $hold_id
        ];
        $response = Http::petapi()->post($url_second, $queryParams_second);
        if ($response->failed()) {
            // echo 'HTTP request failed: ' . $response->status();
        } else {
            $datas = $response->json();

            return $datas;
        }
    }

    public function listTicketByCategory($ticketCategoryName)
    {
        // Find the category by name
        $ticketCategory = TicketCategory::firstWhere('name', $ticketCategoryName);

        // Check if the category exists
        if (!$ticketCategory) {
            // Category not found, handle it by returning a 404 or another error
            return response()->json(['error' => 'Category not found'], 200);
        }

        // Fetch the productions related to the category, order by occurred_at, and limit to 100
        $ticketProductions = $ticketCategory->productions()
            ->with('venue')
            ->orderBy('occurred_at', 'asc')
            ->take(100)
            ->get();

        if ($ticketProductions->isEmpty()) {
            return response()->json(['error' => 'Category not found'], 200);
        }


        // Transform productions into events
        $events = $this->toEvents($ticketProductions);

        // Return the view with events and category
        return $events;
    }

    public function listTicketByPerformer($performerName)
    {
        $ticketProductions = TicketProduction::with(['venue', 'performers'])->where(function ($builder) use ($performerName) {
            $builder->where('name', 'like', '%' . $performerName . '%')
                ->orWhereHas('performers', function ($query) use ($performerName) {
                    $query->where('name', 'like', '%' . $performerName . '%');
                });
        });

        $ticketProductions = $ticketProductions->orderBy('occurred_at', 'asc')->take(100)->get();
        // dd($ticketProductions);
        $events = $this->toEvents($ticketProductions);
        return $events;
    }

    public function getTicketTax(Request $request)
    {
        $tickets = $request->input('ticket');  // get the array of tickets from the input
        $shipping = 15;   // assuming single shipping cost for all tickets
        $results = [];  // To store responses for each ticket

        // Loop through each ticket and send a request for each one
        foreach ($tickets as $ticket) {
            $ticketId = $ticket['id'];
            $price = $ticket['price'];
            $quantity = $ticket['quantity'];

            $queryParams = [
                "quantity"  => $quantity,
                "price"     => $price,
                "shipping"  => $shipping,  // apply the same shipping if applicable
            ];

            $url = '/tickets/' . $ticketId . '/tax-quote';
            $response = Http::petapi()->post($url, $queryParams);

            if ($response->failed()) {
                $results[] = [
                    'ticket_id' => $ticketId,
                    'message' => 'Failed to fetch tax for this ticket. Please try again later.'
                ];
            } else {
                // Add the successful response data to the results array
                $results[] = [
                    'ticket_id' => $ticketId,
                    'data' => $response->json()
                ];
            }
        }

        return $results;  // Return the array of results
    }

    public function checkTicketAvailability(Request $request)
    {
        $search = $request->query('search');

        $url = '/tickets';
        $queryParams = [
            "productionId" => $search
        ];
        // Check if data is cached
        $cacheKey = 'tickets_' . $search;
        $details = Cache::remember($cacheKey, now()->addSecond(1), function () use ($url, $queryParams, $search) {
            $response = Http::petapi()->get($url, $queryParams);

            if ($response->failed()) {
                return false;
            }
            $json_data = $response->json();
            if (!empty($json_data['data'])) {
                return true;
            } elseif (!empty($json_data['ticket_groups'])) {
                return true;
            } else {
                return false;
            }
        });

        if ($details) {
            $data = ["status" => true, "message" => "Ticket Available"];
            return $data;
        } else {
            $data = ["status" => false, "message" => "No Ticket Available"];

            $durationInSeconds = 8;
            $expirationTime = Carbon::now()->addSeconds($durationInSeconds);

            // Data to store in the session
            $data = [
                'message' => 'No Ticket Available',
                'expiration_time' => $expirationTime,
            ];

            // Store the data in the session
            return $data;
        }
    }
}
