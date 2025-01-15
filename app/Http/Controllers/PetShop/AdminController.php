<?php

namespace App\Http\Controllers\PetShop;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\PetShop\Branding;
use App\Models\PetShop\Email;
use App\Models\PetShop\FeatureHotel;
use App\Models\PetShop\FeatureMechandise;
use App\Models\PetShop\FeatureMerchandise;
use App\Models\PetShop\FeatureRental;
use App\Models\PetShop\FeatureTicket;
use App\Models\PetShop\FeatureTour;
use App\Models\Hotel;
use App\Models\PetShop\MenuBar;
use App\Models\Merchandise;
use App\Models\PetShop\NotAllowedHotel;
use App\Models\PetShop\NotAllowedMerchandise;
use App\Models\PetShop\NotAllowedRental;
use App\Models\PetShop\NotAllowedTicket;
use App\Models\PetShop\NotAllowedTour;
use App\Models\PetShop\OrderHistory;
use App\Models\TicketProduction;
use App\Models\TicketVenue;
use App\Models\Tour\Destination as TourDestination;
use App\Models\PetShop\User;
use App\Models\VacationRental;
use App\Models\PetShop\PaymentCharge;
use App\Models\PetShop\PetPoint;
use App\Models\PetShop\UserPetPoints;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    //admin login
    public function login(Request $request)
    {
        $email = $request->email;
        $password = $request->password;
        $admin = Admin::where('email', $email)
            ->first();

        if ($admin) {

            if (password_verify($password, $admin->password)) {
                $token = $admin->createToken('AdminToken')->plainTextToken;
                return response()->json([
                    "status" => "success",
                    'message' => 'Login successful',
                    'token' => $token,
                    'user' => [
                        "name" => $admin['name'],
                        "email" => $admin['email'],
                        "is_super" => $admin['is_super']
                    ]
                ], 200);
                // if ($admin->is_super) {

                // } else {
                // }
            } else {
                $data = ["status" => "fail", "message" => "Email or Password Incorrect", "data" => null];
                return response()->json($data);
            }
        } else {
            $data = ["status" => "fail", "message" => "Can't login", "data" => null];
            return response()->json($data);
            // return (["status" => false, "message" => "no user found", "data" => null]);
        }
    }
    public function logout(Request $request)
    {
        return response()->json([
            'status' => true,
            'message' => 'Admin Logout Successfully',
            'data' => null
        ], 200);
    }
    //create admin
    public function createAdmin(Request $request)
    {
        $admin = Admin::where('email', $request->admin_email)->first();
        $password = password_hash($request->password, PASSWORD_DEFAULT);
        if (!$admin) {
            $admin_id_ = new Admin();
            $admin_id_->name = $request->name;
            $admin_id_->email = $request->admin_email;
            $admin_id_->password = $password;
            $admin_id_->is_super = $request->has('is_super_admin') ? 1 : 0;
            $admin_id_->is_super = $request->has('is_super_admin') ? 1 : 0;
            $admin_id_->save();
            $admin_info = Admin::where('id', $admin_id_->id)->first();
            $response = [
                "status" => true,
                "message" => "Account registered successfully.",
                "data" => $admin_info
            ];
            return response()->json($response);
            // retur?n view('Accounts.registerSuccess', compact('response'));
        } else {
            $response = [
                "status" => false,
                "message" => "Account not registered successfully.",
                "data" => null
            ];
            return response()->json($response);
        }
    }

    //get all admin
    public function getAdmin(Request $request)
    {
        $admins = Admin::select(['id', 'name', 'email'])->paginate(10);
        $response = [
            "status" => true,
            "message" => "Admin Found",
            "data" => $admins
        ];
        return response()->json($response);
    }


    //remove admin
    public function removeAdmin(Request $request)
    {
        Admin::where('id', $request->query('id'))->delete();
        $response = [
            "status" => true,
            "message" => "Admin Remove Succesfully",
            "data" => null
        ];
        return response()->json($response);
    }

    //get all user
    public function getUsers(Request $request)
    {
        $page = $request->query('page', 1); // Get the 'page' parameter from the query string, default to 1 if not present
        $search = $request->query('search'); // Assuming you also have a search parameter

        $users = User::where('first_name', 'like', '%' . $search . '%')
            ->orWhere('last_name', 'like', '%' . $search . '%')
            ->orWhere('email', 'like', '%' . $search . '%')
            ->orWhere('phone', 'like', '%' . $search . '%')
            ->select(['user_id', 'first_name', 'last_name', 'email', 'phone'])
            ->paginate(10, ['*'], 'page', $page); // Paginate with 10 items per page
        $response = [
            "status" => true,
            "message" => "User Found",
            "data" => $users
        ];
        return response()->json($response);;
    }

    //getallOrderHistory
    public function getOrderHistoryOne(Request $request)
    {
        $search = $request->query('search');
        $order_history = OrderHistory::where("transaction_id", 'like', '%' . $search . '%')->orWhere('product_title', 'like', '%' . $search . '%')->orWhere('sku', 'like', '%' . $search . '%')->orWhere('ticket_id', 'like', '%' . $search . '%')->orWhere('hotel_id', 'like', '%' . $search . '%')->paginate(10);
        $response = [
            "status" => true,
            "message" => "Order History Found",
            "data" => $order_history
        ];
        return response()->json($response);
    }

    public function getOrderHistory(Request $request)
    {
        $search = $request->query('search');

        $perPage = $request->query('perPage');
        $order_history = OrderHistory::orWhere("transaction_id", 'like', '%' . $search . '%')->orWhere('product_title', 'like', '%' . $search . '%')->orWhere('sku', 'like', '%' . $search . '%')->orWhere('ticket_id', 'like', '%' . $search . '%')->orWhere('hotel_id', 'like', '%' . $search . '%')->get();
        // $branding = Branding::first();
        // $combinedData = [];
        // Initialize an empty array to hold the combined data temporarily
        $tempData = [];
        $branding = Branding::first();
        // Process each item in the order history
        foreach ($order_history as $item) {
            $transactionId = $item['transaction_id'];
            $user = User::where('user_id', $item->user_id)->select(['first_name', 'email'])->first();
            //    dd($user);
            // Check if the transaction ID already exists in the temporary data array
            if (!isset($tempData[$transactionId])) {
                $tempData[$transactionId] = [
                    'transaction_id' => $transactionId,
                    'created_at' => $item->created_at->toDateTimeString(),
                    'total_price' => 0, // Initialize total price
                    'data' => []
                ];
            }

            // Add the item's total price to the total price for this transaction
            $tempData[$transactionId]['total_price'] += $item->total_price;

            // Append the item details to the data array for this transaction
            $tempData[$transactionId]['data'][] = [
                "id" => $item->id,
                "product_title" => $item->product_title,
                "sku" => $item->sku,
                "quantity" => $item->quantity,
                "retail_price" => $item->retail_price,
                "ticket_id" => $item->ticket_id,
                "hotel_id" => $item->hotel_id,
                "type_of_payment" => $item->type_of_payment,
                "total_price" => $item->total_price,
                "last_four_digit" => $item->last_four_digit,
                'invoice' => $item->invoice,
                'certificate_code' => $item->certificate_code,
                "first_name" => $user->first_name,
                "email" => $user->email,

            ];
        }

        // Now process the tempData as needed
        $combinedData = [];
        foreach ($tempData as $transactionId => $data) {
            $combinedData[] = $data;
        }
        $branding_data = [

            "id" => $branding->id,
            "header_logo" => $branding->header_logo,
            "footer_logo" => $branding->footer_logo,
            "address" => $branding->address,
            "phone_number" => $branding->phone_number,
            "trade_mark" => $branding->trade_mark,
            "term_policy" => $branding->term_policy,
        ];
        // $combinedData is now ready for use


        // Convert associative array to indexed array
        // Parameters for pagination
        if ($perPage == null) {
            $perPage = 25; // Number of items per page
        }
        $page = $request->query('page', 1); // Current page number from query parameter, default to 1 if not present

        // Calculate the offset
        $offset = ($page - 1) * $perPage;

        // Slice the array to get items for the current page
        $paginatedItems = array_slice($combinedData, $offset, $perPage);

        // Create a paginator
        $paginator = new LengthAwarePaginator(
            $paginatedItems, // Current page items
            count($combinedData), // Total number of items
            $perPage, // Items per page
            $page, // Current page
            ['path' => $request->url(), 'query' => $request->query()] // Ensure pagination links have the correct URL and query parameters
        );
        // dd($branding_data );
        //         // Optionally, if you want to debug the paginated data
        // dd($paginator->toArray());
        $response = [
            "status" => true,
            "message" => "User Found",
            "data" => [
                "branding_data" => $branding_data,
                "order_history" => $paginator
            ]
        ];
        return response()->json($response);
    }

    public function orderList(Request $request)
    {


        $search = $request->query('search');

        $perPage = $request->query('perPage');
        // Perform the search query
        $order_history = OrderHistory::orWhere('transaction_id', 'like', '%' . $search . '%')
            ->orWhere('product_title', 'like', '%' . $search . '%')
            ->orWhere('sku', 'like', '%' . $search . '%')
            ->orWhere('ticket_id', 'like', '%' . $search . '%')
            ->orWhere('hotel_id', 'like', '%' . $search . '%')
            ->orderBy('created_at', 'desc')
            ->get();

        // Check if the search query returned any results
        if ($order_history->isEmpty()) {
            // If no results, retrieve all records
            $order_history = OrderHistory::orderBy('created_at', 'desc')->get();
        }
        // $branding = Branding::first();
        // $combinedData = [];
        // Initialize an empty array to hold the combined data temporarily
        $tempData = [];
        $branding = Branding::first();
        // Process each item in the order history
        foreach ($order_history as $item) {
            $transactionId = $item['transaction_id'];
            $user = User::where('user_id', $item->user_id)
                ->select(['first_name', 'email', 'last_name', 'phone'])
                ->first();

            if ($user) {
                // dd($user);
                $matchesSearch = collect([$user->first_name, $user->last_name, $user->email, $user->phone])
                    ->filter(function ($value) use ($search) {
                        return stripos($value, $search) !== false;
                    });

                if (!$matchesSearch->isEmpty()) {

                    // Check if the transaction ID already exists in the temporary data array
                    if (!isset($tempData[$transactionId])) {
                        $tempData[$transactionId] = [
                            'transaction_id' => $transactionId,
                            'created_at' => $item->created_at->toDateTimeString(),
                            'total_price' => 0.0, // Initialize total price
                            'data' => []
                        ];
                    }

                    // Add the item's total price to the total price for this transaction
                    $tempData[$transactionId]['total_price'] += floatval($item->total_price);
                    $merchandise_description = null;
                    if ($item->product_id != null) {
                        $merchandise_description = Merchandise::where('product_id', $item->product_id)->select('description')->first()->description;
                    }
                    // Append the item details to the data array for this transaction
                    $tempData[$transactionId]['data'][] = [
                        "id" => $item->id,
                        "product_title" => $item->product_title,
                        "sku" => $item->sku,
                        "quantity" => $item->quantity,
                        "retail_price" => number_format($item->retail_price, 2),
                        "ticket_id" => $item->ticket_id,
                        "hotel_id" => $item->hotel_id,
                        "type_of_payment" => $item->type_of_payment,
                        "total_price" => $item->total_price,
                        "product_id" => $item->product_id,
                        "last_four_digit" => $item->last_four_digit,
                        'invoice' => $item->invoice,
                        'certificate_code' => $item->certificate_code,
                        "first_name" => $user->first_name,
                        "last_name" => $user->last_name,
                        "merchandise_description" => $merchandise_description,
                        "email" => $user->email,

                    ];
                }
            }
        }
        foreach ($tempData as &$transaction) {
            $transaction['total_price'] = number_format(floatval($transaction['total_price']), 2);
        }
        // Now process the tempData as needed
        $combinedData = [];
        foreach ($tempData as $transactionId => $data) {
            $combinedData[] = $data;
        }
        $branding_data = [

            "id" => $branding->id,
            "header_logo" => $branding->header_logo,
            "footer_logo" => $branding->footer_logo,
            "address" => $branding->address,
            "phone_number" => $branding->phone_number,
            "trade_mark" => $branding->trade_mark,
            "term_policy" => $branding->term_policy,
        ];
        // $combinedData is now ready for use


        // Convert associative array to indexed array
        // Parameters for pagination
        if ($perPage == null) {
            $perPage = 25;
        }
        // Number of items per page
        $page = $request->query('page', 1); // Current page number from query parameter, default to 1 if not present

        // Calculate the offset
        $offset = ($page - 1) * $perPage;

        // Slice the array to get items for the current page
        $paginatedItems = array_slice($combinedData, $offset, $perPage);

        // Create a paginator
        $paginator = new LengthAwarePaginator(
            $paginatedItems, // Current page items
            count($combinedData), // Total number of items
            $perPage, // Items per page
            $page, // Current page
            ['path' => $request->url(), 'query' => $request->query()] // Ensure pagination links have the correct URL and query parameters
        );
        $response = [
            "status" => true,
            "message" => "User Found",
            "data" => [
                "branding_data" => $branding_data,
                "order_history" => $paginator
            ]
        ];
        return response()->json($response);
    }


    //featureHotels
    public function insertfeatureHotel(Request $request)
    {
        $hotel_id = $request->query('hotel_id');
        $hotel = new FeatureHotel();
        $hotel->hotel_id = $hotel_id;
        $hotel->save();
        $data = [
            "status" => true,
            "message" => 'update sucessfully'
        ];
        return response()->json($data);
    }

    public function deletefeatureHotel(Request $request)
    {
        $hotel_id = $request->query('hotel_id');

        FeatureHotel::where('hotel_id', $hotel_id)->delete();
        $data = [
            "status" => true,
            "message" => 'deleted sucessfully'
        ];
        return response()->json($data);
    }
    public function insertNotAllowedHotel(Request $request)
    {
        $hotel_id = $request->query('hotel_id');


        $not_allowed_hotel = new NotAllowedHotel();
        $not_allowed_hotel->hotel_id = $hotel_id;
        $not_allowed_hotel->save();
        $data = [
            "status" => true,
            "message" => 'update sucessfully'
        ];
        return response()->json($data);
    }

    public function deleteNotAllowedHotel(Request $request)
    {
        $hotel_id = $request->query('hotel_id');
        NotAllowedHotel::where('hotel_id', $hotel_id)->delete();
        $data = [
            "status" => true,
            "message" => 'deleted sucessfully'
        ];
        return response()->json($data);
    }
    //fetch-hotel
    public function fetchHotel(Request $request)
    {
        $search = $request->query('search');
        $rating = $request->query('rating');
        $perPage = $request->query('perPage');
        // dd($rating);
        // dd($rating);
        $is_not_allowed = $request->query('is_not_allowed');
        if ($is_not_allowed != null) {
            if ($is_not_allowed == "true") {
                $is_not_allowed = true;
            } else {
                $is_not_allowed = false;
            }
        }
        $is_featured = $request->query('is_featured');
        if ($is_featured != null) {
            if ($is_featured == "true") {
                $is_featured = true;
            } else {
                $is_featured = false;
            }
        }
        $feature_hotels = FeatureHotel::select('hotel_id', 'id')->get()->toArray();
        $feature_hotels_map = collect($feature_hotels)->pluck('id', 'hotel_id');

        // Assuming NotAllowedHotel has columns hotel_id and id (as not_allowed_id)
        $not_allowed_hotels = NotAllowedHotel::select('hotel_id', 'id')->get()->toArray();
        $not_allowed_hotels_map = collect($not_allowed_hotels)->pluck('id', 'hotel_id');
        $hotels = Hotel::where(function ($query) use ($search) {
            $query->where('giata_id', 'like', '%' . $search . '%')
                ->orWhere('name', 'like', '%' . $search . '%')
                ->orWhere('city', 'like', '%' . $search . '%');
        });

        if ($rating !== null) {
            $hotels->where('rating', intval($rating));
        }

        $hotels = $hotels->select(['id', 'name', 'city', 'giata_id', 'rating'])
            ->get();

        // dd($hotels->toArray());
        if ($is_featured !== null) {
            $hotels = $hotels->filter(function ($hotel) use ($feature_hotels_map, $is_featured) {
                //   dd(  isset($feature_hotels_map[$hotel->id]));
                if ($is_featured == isset($feature_hotels_map[$hotel->id])) {
                    return $hotel;
                }
            });
        }

        if ($is_not_allowed !== null) {
            $hotels = $hotels->filter(function ($hotel) use ($not_allowed_hotels_map, $is_not_allowed) {
                //   dd(  isset($feature_hotels_map[$hotel->id]));
                if ($is_not_allowed == isset($not_allowed_hotels_map[$hotel->id])) {
                    return $hotel;
                }
            });
        }
        // Add is_featured, feature_id, is_not_allowed, and not_allowed_id attributes to each hotel
        $hotels->transform(function ($hotel) use ($feature_hotels_map, $not_allowed_hotels_map) {
            $hotel->is_featured = isset($feature_hotels_map[$hotel->id]);
            $hotel->feature_id = $feature_hotels_map[$hotel->id] ?? null;
            $hotel->is_not_allowed = isset($not_allowed_hotels_map[$hotel->id]);
            $hotel->not_allowed_id = $not_allowed_hotels_map[$hotel->id] ?? null;
            return $hotel;
        });
        $page = LengthAwarePaginator::resolveCurrentPage();
        if ($perPage == null) {
            $perPage = 25;
        }
        $paginatedHotels = new LengthAwarePaginator(
            $hotels->forPage($page, $perPage)->values(),
            $hotels->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
        $data = [
            "status" => true,
            "message" => 'Hotel data found',
            "data" => $paginatedHotels
        ];
        return response()->json($data);
    }


    public function getBranding()
    {
        $branding = Branding::get()->toArray();
        $details = [
            "stripe_key" => config('app.petshop_stripe_key'),
            "stripe_secret" => config('app.petshop_stripe_secret'),
            "petapikey" => config('app.petapikey'),
            "petid" => config('app.petid'),
            "peturl" => config('app.peturl'),
            "mail_mailer" => config('app.petshop_mail_mailer'),
            "mail_host" => config('app.petshop_mail_host'),
            "mail_port" => config('app.petshop_mail_port'),
            "mail_username" => config('app.petshop_mail_username'),
            "mail_password" => config('app.petshop_mail_password'),
            "mail_encryption" => config('app.petshop_mail_encryption'),
            "mail_from_address" => config('app.petshop_mail_from_address'),
            "mail_from_name" => config('app.petshop_mail_from_name'),
            "mail_support_address" => config('app.petshop_mail_support_address'),
            "mail_support_name" => config('app.petshop_mail_support_name'),

        ];
        $paymentCharges = PaymentCharge::get();
        // dd($paymentCharges);
        $data = [
            "branding" => $branding,
            "details" => $details,
            "paymentCharges" => $paymentCharges
        ];
        return response()->json($data);;
    }
    public function updateBranding(Request $request, $id)
    {
        // Retrieve the branding record
        $branding = Branding::where('id', $id)->first();
        if ($branding) {
            // Update the fields
            if ($request->input('address') != null) {
                $branding->address = $request->input('address');
            }

            if ($request->input('header_color') != null) {
                $branding->header_color = $request->input('header_color');
            }


            if ($request->input('footer_color') != null) {
                $branding->footer_color = $request->input('footer_color');
            }
            if ($request->input('phone_number') != null) {

                $branding->phone_number = $request->input('phone_number');
            }
            if ($request->input('trade_mark') != null) {

                $branding->trade_mark = $request->input('trade_mark');
            }
            if ($request->input('terms_policy') != null) {
                $branding->term_policy = $request->input('terms_policy');
            }
            // Handle header_logo update if present in request
            if ($request->hasFile('header_logo')) {
                // Store the new header_logo
                //$headerLogoPath = $request->file('header_logo')->store('public/logos');
                // Delete the previous header_logo if exists
                //if ($branding->header_logo) {
                //    Storage::delete($branding->header_logo);
                //}
                $headerLogoPath = $request->file('header_logo')->hashName();
                $request->file('header_logo')->move(public_path('storage/logos'), $headerLogoPath);
                $branding->header_logo = "storage/logos/" . basename($headerLogoPath);
            }

            // Handle footer_logo update if present in request
            if ($request->hasFile('footer_logo')) {
                // Store the new footer_logo
                //$footerLogoPath = $request->file('footer_logo')->store('public/logos');

                // Delete the previous footer_logo if exists
                //if ($branding->footer_logo) {
                //    Storage::delete($branding->footer_logo);
                //}
                $footerLogoPath = $request->file('footer_logo')->hashName();
                $request->file('footer_logo')->move(public_path('storage/logos'), $footerLogoPath);
                $branding->footer_logo = "storage/logos/" . basename($footerLogoPath);
            }
            if ($request->input('linkedin_url') != null) {
                $branding->linkedin_url = $request->input('linkedin_url');
            }

            if ($request->input('twitter_url') != null) {
                $branding->twitter_url = $request->input('twitter_url');
            }
            if ($request->input('facebook_url') != null) {
                $branding->facebook_url = $request->input('facebook_url');
            }
            // Save the changes
            $branding->save();
        } else {
            $footerLogoPath = $request->file('footer_logo')->store('public/logos');
            $headerLogoPath = $request->file('header_logo')->store('public/logos');
            $branding_new = new Branding();
            $branding_new->address = $request->input('address');
            $branding_new->phone_number = $request->input('phone_number');
            $branding_new->trade_mark = $request->input('trade_mark');
            $branding_new->header_color = $request->input('header_color');
            $branding_new->footer_color = $request->input('footer_color');

            $branding_new->term_policy = $request->input('terms_policy');
            $branding_new->header_logo = "storage/logos/" . basename($headerLogoPath);
            $branding_new->footer_logo = "storage/logos/" . basename($footerLogoPath);
            $branding_new->save();
        }
        // Return success response
        return response()->json(['message' => 'Branding updated successfully'], 200);
    }

    public function hideMenu(Request $request, $id)
    {
        $is_active = $request->is_active;
        $menu = MenuBar::where('id', $id)->update(['is_active' => $is_active]);
        return 'action done sucessfully';
    }

    public function getMenu()
    {
        $menu = MenuBar::get();
        return  $menu->toArray();
    }

    public function fetchMerchandise(Request $request)
    {
        $search = $request->query('search');
        $is_not_allowed = $request->query('is_not_allowed');

        $perPage = $request->query('perPage');
        if ($is_not_allowed != null) {
            if ($is_not_allowed == "true") {
                $is_not_allowed = true;
            } else {
                $is_not_allowed = false;
            }
        }
        $is_featured = $request->query('is_featured');
        if ($is_featured != null) {
            if ($is_featured == "true") {
                $is_featured = true;
            } else {
                $is_featured = false;
            }
        }
        $feature_merchandises = FeatureMerchandise::select('merchandise_id', 'id')->get()->toArray();
        $feature_merchandises_map = collect($feature_merchandises)->pluck('id', 'merchandise_id');

        // Assuming NotAllowedHotel has columns hotel_id and id (as not_allowed_id)
        $not_allowed_merchandises = NotAllowedMerchandise::select('merchandise_id', 'id')->get()->toArray();
        $not_allowed_merchandises_map = collect($not_allowed_merchandises)->pluck('id', 'merchandise_id');
        $merchandise = Merchandise::where(function ($query) use ($search) {
            $query->where('brand', 'like', '%' . $search . '%')
                ->orWhere('name', 'like', '%' . $search . '%')
                ->orWhere('upc', 'like', '%' . $search . '%')
                ->orWhere('model', 'like', '%' . $search . '%');
        });

        $merchandise = $merchandise->select(['id', 'name', 'brand', 'model', 'upc'])
            ->get();

        // dd($merchandise->toArray());
        if ($is_featured !== null) {
            $merchandise = $merchandise->filter(function ($merchandise) use ($feature_merchandises_map, $is_featured) {
                //   dd(  isset($feature_hotels_map[$merchandise->id]));
                if ($is_featured == isset($feature_merchandises_map[$merchandise->id])) {
                    return $merchandise;
                }
            });
        }

        if ($is_not_allowed !== null) {
            $merchandise = $merchandise->filter(function ($merchandise) use ($not_allowed_merchandises_map, $is_not_allowed) {
                //   dd(  isset($feature_hotels_map[$merchandise->id]));
                if ($is_not_allowed == isset($not_allowed_merchandises_map[$merchandise->id])) {
                    return $merchandise;
                }
            });
        }
        // Add is_featured, feature_id, is_not_allowed, and not_allowed_id attributes to each hotel
        $merchandise->transform(function ($merchandise) use ($feature_merchandises_map, $not_allowed_merchandises_map) {
            $merchandise->is_featured = isset($feature_merchandises_map[$merchandise->id]);
            $merchandise->feature_id = $feature_merchandises_map[$merchandise->id] ?? null;
            $merchandise->is_not_allowed = isset($not_allowed_merchandises_map[$merchandise->id]);
            $merchandise->not_allowed_id = $not_allowed_merchandises_map[$merchandise->id] ?? null;
            return $merchandise->toArray();
        });
        $page = LengthAwarePaginator::resolveCurrentPage();
        if ($perPage == null) {
            $perPage = 25;
        }
        $paginatedHotels = new LengthAwarePaginator(
            $merchandise->forPage($page, $perPage)->values(),
            $merchandise->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
        $data = [
            "status" => true,
            "message" => 'data found',
            "data" => $paginatedHotels
        ];
        return response()->json($data);
    }
    public function insertFeatureMerchandise(Request $request)
    {
        $merchandise_id = $request->query('merchandise_id');
        $hotel = new FeatureMerchandise();
        $hotel->merchandise_id = $merchandise_id;
        $hotel->save();
        $data = [
            "status" => true,
            "message" => 'update sucessfully'
        ];
        return response()->json($data);
    }
    public function insertNotAllowedMerchandise(Request $request)
    {

        $merchandise_id = $request->query('merchandise_id');
        $not_allowed_hotel = new NotAllowedMerchandise();
        $not_allowed_hotel->merchandise_id = $merchandise_id;
        $not_allowed_hotel->save();
        $data = [
            "status" => true,
            "message" => 'update sucessfully'
        ];
        return response()->json($data);
    }
    public function deleteFeatureMerchandise(Request $request)
    {
        $merchandise_id = $request->query('merchandise_id');
        FeatureMerchandise::where('merchandise_id', $merchandise_id)->delete();
        $data = [
            "status" => true,
            "message" => 'deleted sucessfully'
        ];
        return response()->json($data);
    }
    public function deleteNotAllowedMerchandise(Request $request)
    {

        $merchandise_id = $request->query('merchandise_id');
        NotAllowedMerchandise::where('merchandise_id', $merchandise_id)->delete();
        $data = [
            "status" => true,
            "message" => 'deleted sucessfully'
        ];
        return response()->json($data);
    }


    public function fetchTicket(Request $request)
    {
        $search = $request->query('search');
        $is_not_allowed = $request->query('is_not_allowed');

        $perPage = $request->query('perPage');
        if ($is_not_allowed != null) {
            if ($is_not_allowed == "true") {
                $is_not_allowed = true;
            } else {
                $is_not_allowed = false;
            }
        }
        $is_featured = $request->query('is_featured');
        if ($is_featured != null) {
            if ($is_featured == "true") {
                $is_featured = true;
            } else {
                $is_featured = false;
            }
        }
        $feature_ticket = FeatureTicket::select('ticket_id', 'id')->get()->toArray();
        $feature_ticket_map = collect($feature_ticket)->pluck('id', 'ticket_id');

        // Assuming NotAllowedHotel has columns hotel_id and id (as not_allowed_id)
        $not_allowed_ticket = NotAllowedTicket::select('ticket_id', 'id')->get()->toArray();
        $not_allowed_ticket_map = collect($not_allowed_ticket)->pluck('id', 'ticket_id');
        $ticket = TicketProduction::with(['venue:id,venue_id,name,city,popularity_score', 'category:category_id,name'])
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('venue', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%')
                            ->orWhere('city', 'like', '%' . $search . '%');
                    })->orWhereHas('category', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    });
            })
            ->select(['id', 'name', 'venue_id', 'category_id']) // Include venue_id to join with venue
            ->get();

        if ($is_featured !== null) {
            $ticket = $ticket->filter(function ($ticket) use ($feature_ticket_map, $is_featured) {
                //   dd(  isset($feature_hotels_map[$ticket->id]));
                if ($is_featured == isset($feature_ticket_map[$ticket->id])) {
                    return $ticket;
                }
            });
        }

        if ($is_not_allowed !== null) {
            $ticket = $ticket->filter(function ($ticket) use ($not_allowed_ticket_map, $is_not_allowed) {
                //   dd(  isset($feature_hotels_map[$ticket->id]));
                if ($is_not_allowed == isset($not_allowed_ticket_map[$ticket->id])) {
                    return $ticket;
                }
            });
        }
        // Add is_featured, feature_id, is_not_allowed, and not_allowed_id attributes to each hotel
        $ticket->transform(function ($ticket) use ($feature_ticket_map, $not_allowed_ticket_map) {
            $ticket->is_featured = isset($feature_ticket_map[$ticket->id]);
            $ticket->feature_id = $feature_ticket_map[$ticket->id] ?? null;
            $ticket->is_not_allowed = isset($not_allowed_ticket_map[$ticket->id]);
            $ticket->not_allowed_id = $not_allowed_ticket_map[$ticket->id] ?? null;
            return $ticket;
        });
        $page = LengthAwarePaginator::resolveCurrentPage();
        if ($perPage == null) {
            $perPage = 25;
        }
        $paginatedHotels = new LengthAwarePaginator(
            $ticket->forPage($page, $perPage)->values(),
            $ticket->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
        // dd($paginatedHotels->toArray());
        $data = [
            "status" => true,
            "message" => 'data found',
            "data" => $paginatedHotels
        ];
        return response()->json($data);
    }
    public function insertfeatureTicket(Request $request)
    {
        $ticket_id = $request->query('ticket_id');
        $hotel = new FeatureTicket();
        $hotel->ticket_id = $ticket_id;
        $hotel->save();
        $data = [
            "status" => true,
            "message" => 'update sucessfully'
        ];
        return response()->json($data);
    }
    public function insertNotAllowedTicket(Request $request)
    {

        $ticket_id = $request->query('ticket_id');
        $not_allowed_hotel = new NotAllowedTicket();
        $not_allowed_hotel->ticket_id = $ticket_id;
        $not_allowed_hotel->save();
        $data = [
            "status" => true,
            "message" => 'update sucessfully'
        ];
        return response()->json($data);
    }
    public function deletefeatureTicket(Request $request)
    {
        $ticket_id = $request->query('ticket_id');
        FeatureTicket::where('ticket_id', $ticket_id)->delete();
        $data = [
            "status" => true,
            "message" => 'deleted sucessfully'
        ];
        return response()->json($data);
    }
    public function deleteNotAllowedTicket(Request $request)
    {

        $ticket_id = $request->query('ticket_id');
        NotAllowedTicket::where('ticket_id', $ticket_id)->delete();
        $data = [
            "status" => true,
            "message" => 'deleted sucessfully'
        ];
        return response()->json($data);
    }

    public function updateConfigData(Request $request)
    {
        $stripe_key = $request->stripe_key;
        $stripe_secret = $request->stripe_secret;
        $type = $request->type;
        $petapikey = $request->petapikey;
        $petid = $request->petid;
        $peturl = $request->peturl;
        $mail_mailer = $request->mail_mailer;
        $mail_host = $request->mail_host;
        $mail_port = $request->mail_port;
        $mail_username = $request->mail_username;
        $mail_password = $request->mail_password;
        $mail_encryption = $request->mail_encryption;
        $mail_from_address = $request->mail_from_address;
        $mail_from_name = $request->mail_from_name;
        $mail_support_address = $request->mail_support_address;
        $mail_support_name = $request->mail_support_name;

        if ($type === 'config') {
            $this->updateConfig('petshop_stripe_key', $stripe_key);
            $this->updateConfig('petshop_stripe_secret', $stripe_secret);
            $this->updateConfig('petapikey', $petapikey);
            $this->updateConfig('petid', $petid);
            $this->updateConfig('peturl', $peturl);
            $this->updateConfig('mail_mailer', $mail_mailer);
            $this->updateConfig('mail_host', $mail_host);
            $this->updateConfig('mail_port', $mail_port);
            $this->updateConfig('mail_username', $mail_username);
            $this->updateConfig('mail_password', $mail_password);
            $this->updateConfig('mail_encryption', $mail_encryption);
            $this->updateConfig('mail_from_address', $mail_from_address);
            $this->updateConfig('mail_from_name', $mail_from_name);
            $this->updateConfig('mail_support_address', $mail_support_address);
            $this->updateConfig('mail_support_name', $mail_support_name);
        }
        if ($type == 'env') {
            $this->updateEnv('MAIL_MAILER', $mail_mailer);
            $this->updateEnv('MAIL_HOST', $mail_host);
            $this->updateEnv('MAIL_PORT', $mail_port);
            $this->updateEnv('MAIL_USERNAME', $mail_username);
            $this->updateEnv('MAIL_PASSWORD', $mail_password);
            $this->updateEnv('MAIL_ENCRYPTION', $mail_encryption);
            $this->updateEnv('MAIL_FROM_ADDRESS', $mail_from_address);
            $this->updateEnv('MAIL_FROM_NAME', $mail_from_name);
            $this->updateEnv('MAIL_SUPPORT_ADDRESS', $mail_support_address);
            $this->updateEnv('MAIL_SUPPORT_NAME', $mail_support_name);
        }
        response()->json(['message' => 'Configuration updated successfully']);
        // return response()->json(['message' => 'Invalid type'], 400);
    }

    protected function updateConfig($key, $value)
    {
        $configFile = config_path('app.php'); // Adjust the config file path as needed
        $configContents = File::get($configFile);

        $pattern = "/('" . $key . "'\s*=>\s*).+?,/";
        $replacement = "'" . $key . "' => '" . $value . "',";

        $newContents = preg_replace($pattern, $replacement, $configContents);

        if ($newContents !== null) {
            File::put($configFile, $newContents);
            Artisan::call('config:cache');
            Artisan::call('config:clear');
            return response()->json(['message' => 'Configuration updated successfully']);
        }

        return response()->json(['message' => 'Failed to update configuration'], 500);
    }

    protected function updateEnv($key, $value)
    {
        $envFile = base_path('.env');

        // Read the contents of the .env file
        $envContents = File::get($envFile);

        $pattern = "/^" . $key . "=.*$/m";
        $replacement = $key . "=" . $value;

        if (preg_match($pattern, $envContents)) {
            $newContents = preg_replace($pattern, $replacement, $envContents);
        } else {
            $newContents = $envContents . "\n" . $replacement;
        }

        File::put($envFile, $newContents);
        Artisan::call('config:clear');
        Artisan::call('config:cache');
        return response()->json(['message' => '.env updated successfully']);
    }

    // public function getConfigData(){
    //     $data=[
    //         "stripe_key"=>config('app.stripe_key'),
    //         "stripe_secret"=>config('app.stripe_secret')
    //     ];
    //     dd($data);
    // }

    public function updateBulkHotel(Request $request)
    {
        $id_array = $request->ids;
        $type = $request->type;
        // ['allowed','not_allowed','feature','not_feature'];
        foreach ($id_array as $id) {
            if ($type == 'feature') {
                $not_allowed_hotel = NotAllowedHotel::where('hotel_id', $id)->first();
                if ($not_allowed_hotel) {
                    $not_allowed_hotel->delete();
                }
                $feature_hotel = FeatureHotel::where('hotel_id', $id)->first();
                if (!$feature_hotel) {
                    $feature_hotel = new FeatureHotel();
                    $feature_hotel->hotel_id = $id;
                    $feature_hotel->save();
                }
            } else if ($type == 'not_feature') {
                $feature_hotel = FeatureHotel::where('hotel_id', $id)->first();
                if ($feature_hotel) {
                    $feature_hotel->delete();
                }
            } else if ($type == 'not_allowed') {
                $feature_hotel = FeatureHotel::where('hotel_id', $id)->first();
                if ($feature_hotel) {
                    $feature_hotel->delete();
                }
                $not_allowed_hotel = NotAllowedHotel::where('hotel_id', $id)->first();
                if (!$not_allowed_hotel) {
                    $not_allowed_hotel = new NotAllowedHotel();
                    $not_allowed_hotel->hotel_id = $id;
                    $not_allowed_hotel->save();
                }
            } else if ($type == 'allowed') {
                $not_allowed_hotel = NotAllowedHotel::where('hotel_id', $id)->first();
                if ($not_allowed_hotel) {
                    $not_allowed_hotel->delete();
                }
            } else {
                echo ('invalid type');
            }
        }
        $data = [
            "status" => true,
            "message" => 'update sucessfully'
        ];
        return response()->json($data);
    }

    public function updateBulkTicket(Request $request)
    {
        $id_array = $request->ids;
        $type = $request->type;
        // ['allowed','not_allowed','feature','not_feature'];
        foreach ($id_array as $id) {
            if ($type == 'feature') {
                $not_allowed_ticket = NotAllowedTicket::where('ticket_id', $id)->first();
                if ($not_allowed_ticket) {
                    $not_allowed_ticket->delete();
                }
                $feature_ticket = FeatureTicket::where('ticket_id', $id)->first();
                if (!$feature_ticket) {
                    $feature_ticket = new FeatureTicket();
                    $feature_ticket->ticket_id = $id;
                    $feature_ticket->save();
                }
            } else if ($type == 'not_feature') {
                $feature_ticket = FeatureTicket::where('ticket_id', $id)->first();
                if ($feature_ticket) {
                    $feature_ticket->delete();
                }
            } else if ($type == 'not_allowed') {
                $feature_ticket = FeatureTicket::where('ticket_id', $id)->first();
                if ($feature_ticket) {
                    $feature_ticket->delete();
                }
                $not_allowed_ticket = NotAllowedTicket::where('ticket_id', $id)->first();
                if (!$not_allowed_ticket) {
                    $not_allowed_ticket = new NotAllowedTicket();
                    $not_allowed_ticket->ticket_id = $id;
                    $not_allowed_ticket->save();
                }
            } else if ($type == 'allowed') {
                $not_allowed_ticket = NotAllowedTicket::where('ticket_id', $id)->first();
                if ($not_allowed_ticket) {
                    $not_allowed_ticket->delete();
                }
            } else {
                echo ('invalid type');
            }
        }
        $data = [
            "status" => true,
            "message" => 'update sucessfully'
        ];
        return response()->json($data);
    }
    public function updateBulkMerchandise(Request $request)
    {
        $id_array = $request->ids;
        $type = $request->type;
        // ['allowed','not_allowed','feature','not_feature'];
        foreach ($id_array as $id) {
            if ($type == 'feature') {
                $not_allowed_merchandise = NotAllowedMerchandise::where('merchandise_id', $id)->first();
                if ($not_allowed_merchandise) {
                    $not_allowed_merchandise->delete();
                }
                $feature_merchandise = FeatureMerchandise::where('merchandise_id', $id)->first();
                if (!$feature_merchandise) {
                    $feature_merchandise = new FeatureMerchandise();
                    $feature_merchandise->merchandise_id = $id;
                    $feature_merchandise->save();
                }
            } else if ($type == 'not_feature') {
                $feature_merchandise = FeatureMerchandise::where('merchandise_id', $id)->first();
                if ($feature_merchandise) {
                    $feature_merchandise->delete();
                }
            } else if ($type == 'not_allowed') {
                $feature_merchandise = FeatureMerchandise::where('merchandise_id', $id)->first();
                if ($feature_merchandise) {
                    $feature_merchandise->delete();
                }
                $not_allowed_merchandise = NotAllowedMerchandise::where('merchandise_id', $id)->first();
                if (!$not_allowed_merchandise) {
                    $not_allowed_merchandise = new NotAllowedMerchandise();
                    $not_allowed_merchandise->merchandise_id = $id;
                    $not_allowed_merchandise->save();
                }
            } else if ($type == 'allowed') {
                $not_allowed_merchandise = NotAllowedMerchandise::where('merchandise_id', $id)->first();
                if ($not_allowed_merchandise) {
                    $not_allowed_merchandise->delete();
                }
            } else {
                echo ('invalid type');
            }
        }
        $data = [
            "status" => true,
            "message" => 'update sucessfully'
        ];
        return response()->json($data);
    }

    public function fetchRental(Request $request)
    {
        $search = $request->query('search');
        $is_not_allowed = $request->query('is_not_allowed');

        $perPage = $request->query('perPage');
        if ($is_not_allowed != null) {
            if ($is_not_allowed == "true") {
                $is_not_allowed = true;
            } else {
                $is_not_allowed = false;
            }
        }
        $is_featured = $request->query('is_featured');
        if ($is_featured != null) {
            if ($is_featured == "true") {
                $is_featured = true;
            } else {
                $is_featured = false;
            }
        }
        $feature_rentals = FeatureRental::select('rental_id', 'id')->get()->toArray();
        $feature_rentals_map = collect($feature_rentals)->pluck('id', 'rental_id');

        // Assuming NotAllowedHotel has columns hotel_id and id (as not_allowed_id)
        $not_allowed_rentals = NotAllowedRental::select('rental_id', 'id')->get()->toArray();
        $not_allowed_rentals_map = collect($not_allowed_rentals)->pluck('id', 'rental_id');
        $rentals = VacationRental::where(function ($query) use ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('address1', 'like', '%' . $search . '%');
        });

        $rentals = $rentals->select(['id', 'name', 'address1'])
            ->get();

        // dd($rentals->toArray());
        if ($is_featured !== null) {
            $rentals = $rentals->filter(function ($rentals) use ($feature_rentals_map, $is_featured) {
                //   dd(  isset($feature_hotels_map[$rentals->id]));
                if ($is_featured == isset($feature_rentals_map[$rentals->id])) {
                    return $rentals;
                }
            });
        }

        if ($is_not_allowed !== null) {
            $rentals = $rentals->filter(function ($rentals) use ($not_allowed_rentals_map, $is_not_allowed) {
                //   dd(  isset($feature_hotels_map[$rentals->id]));
                if ($is_not_allowed == isset($not_allowed_rentals_map[$rentals->id])) {
                    return $rentals;
                }
            });
        }
        // Add is_featured, feature_id, is_not_allowed, and not_allowed_id attributes to each hotel
        $rentals->transform(function ($rentals) use ($feature_rentals_map, $not_allowed_rentals_map) {
            $rentals->is_featured = isset($feature_rentals_map[$rentals->id]);
            $rentals->feature_id = $feature_rentals_map[$rentals->id] ?? null;
            $rentals->is_not_allowed = isset($not_allowed_rentals_map[$rentals->id]);
            $rentals->not_allowed_id = $not_allowed_rentals_map[$rentals->id] ?? null;
            return $rentals;
        });
        $page = LengthAwarePaginator::resolveCurrentPage();
        if ($perPage == null) {
            $perPage = 25;
        }
        $paginatedRentals = new LengthAwarePaginator(
            $rentals->forPage($page, $perPage)->values(),
            $rentals->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
        $data = [
            "status" => true,
            "message" => 'data found',
            "data" => $paginatedRentals
        ];
        return response()->json($data);
    }

    public function insertFeatureRentals(Request $request)
    {
        $rental_id = $request->query('rental_id');
        $rental = new FeatureRental();
        $rental->rental_id = $rental_id;
        $rental->save();
        $data = [
            "status" => true,
            "message" => 'update sucessfully'
        ];
        return response()->json($data);
    }

    public function insertNotAllowedRentals(Request $request)
    {

        $rental_id = $request->query('rental_id');
        $not_allowed_rental = new NotAllowedRental();
        $not_allowed_rental->rental_id = $rental_id;
        $not_allowed_rental->save();
        $data = [
            "status" => true,
            "message" => 'update sucessfully'
        ];
        return response()->json($data);
    }
    public function deleteFeatureRental(Request $request)
    {
        $rental_id = $request->query('rental_id');
        FeatureRental::where('rental_id', $rental_id)->delete();
        $data = [
            "status" => true,
            "message" => 'deleted sucessfully'
        ];
        return response()->json($data);
    }
    public function deleteNotAllowedRental(Request $request)
    {

        $rental_id = $request->query('rental_id');
        NotAllowedRental::where('rental_id', $rental_id)->delete();
        $data = [
            "status" => true,
            "message" => 'deleted sucessfully'
        ];
        return response()->json($data);
    }

    public function updateBulkRental(Request $request)
    {
        $id_array = $request->ids;
        $type = $request->type;
        // ['allowed','not_allowed','feature','not_feature'];
        foreach ($id_array as $id) {
            if ($type == 'feature') {
                $not_allowed_rental = NotAllowedRental::where('rental_id', $id)->first();
                if ($not_allowed_rental) {
                    $not_allowed_rental->delete();
                }
                $feature_rental = FeatureRental::where('rental_id', $id)->first();
                if (!$feature_rental) {
                    $feature_rental = new FeatureRental();
                    $feature_rental->rental_id = $id;
                    $feature_rental->save();
                }
            } else if ($type == 'not_feature') {
                $feature_rental = FeatureRental::where('rental_id', $id)->first();
                if ($feature_rental) {
                    $feature_rental->delete();
                }
            } else if ($type == 'not_allowed') {
                $feature_rental = FeatureRental::where('rental_id', $id)->first();
                if ($feature_rental) {
                    $feature_rental->delete();
                }
                $not_allowed_rental = NotAllowedRental::where('rental_id', $id)->first();
                if (!$not_allowed_rental) {
                    $not_allowed_rental = new NotAllowedRental();
                    $not_allowed_rental->rental_id = $id;
                    $not_allowed_rental->save();
                }
            } else if ($type == 'allowed') {
                $not_allowed_rental = NotAllowedRental::where('rental_id', $id)->first();
                if ($not_allowed_rental) {
                    $not_allowed_rental->delete();
                }
            } else {
                echo ('invalid type');
            }
        }
        $data = [
            "status" => true,
            "message" => 'update sucessfully'
        ];
        return response()->json($data);
    }

    public function fetchTour(Request $request)
    {
        $search = $request->query('search');
        $is_not_allowed = $request->query('is_not_allowed');

        $perPage = $request->query('perPage');
        if ($is_not_allowed != null) {
            if ($is_not_allowed == "true") {
                $is_not_allowed = true;
            } else {
                $is_not_allowed = false;
            }
        }
        $is_featured = $request->query('is_featured');
        if ($is_featured != null) {
            if ($is_featured == "true") {
                $is_featured = true;
            } else {
                $is_featured = false;
            }
        }
        $feature_tours = FeatureTour::select('tour_id', 'id')->get()->toArray();
        $feature_tours_map = collect($feature_tours)->pluck('id', 'tour_id');

        // Assuming NotAllowedHotel has columns hotel_id and id (as not_allowed_id)
        $not_allowed_tours = NotAllowedTour::select('tour_id', 'id')->get()->toArray();
        $not_allowed_tours_map = collect($not_allowed_tours)->pluck('id', 'tour_id');
        $tours = TourDestination::where(function ($query) use ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        });

        $tours = $tours->select(['id', 'name'])
            ->get();

        // dd($tours->toArray());
        if ($is_featured !== null) {
            $tours = $tours->filter(function ($tours) use ($feature_tours_map, $is_featured) {
                //   dd(  isset($feature_hotels_map[$tours->id]));
                if ($is_featured == isset($feature_tours_map[$tours->id])) {
                    return $tours;
                }
            });
        }

        if ($is_not_allowed !== null) {
            $tours = $tours->filter(function ($tours) use ($not_allowed_tours_map, $is_not_allowed) {
                //   dd(  isset($feature_hotels_map[$tours->id]));
                if ($is_not_allowed == isset($not_allowed_tours_map[$tours->id])) {
                    return $tours;
                }
            });
        }
        // Add is_featured, feature_id, is_not_allowed, and not_allowed_id attributes to each hotel
        $tours->transform(function ($tours) use ($feature_tours_map, $not_allowed_tours_map) {
            $tours->is_featured = isset($feature_tours_map[$tours->id]);
            $tours->feature_id = $feature_tours_map[$tours->id] ?? null;
            $tours->is_not_allowed = isset($not_allowed_tours_map[$tours->id]);
            $tours->not_allowed_id = $not_allowed_tours_map[$tours->id] ?? null;
            return $tours;
        });
        $page = LengthAwarePaginator::resolveCurrentPage();
        if ($perPage == null) {
            $perPage = 25;
        }
        $paginatedTours = new LengthAwarePaginator(
            $tours->forPage($page, $perPage)->values(),
            $tours->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
        $data = [
            "status" => true,
            "message" => 'data found',
            "data" => $paginatedTours
        ];
        return response()->json($data);
    }

    public function insertFeatureTour(Request $request)
    {
        $tour_id = $request->query('tour_id');
        $tour = new FeatureTour();
        $tour->tour_id = $tour_id;
        $tour->save();
        $data = [
            "status" => true,
            "message" => 'update sucessfully'
        ];
        return response()->json($data);
    }

    public function insertNotAllowedTour(Request $request)
    {

        $tour_id = $request->query('tour_id');
        $not_allowed_tour = new NotAllowedTour();
        $not_allowed_tour->tour_id = $tour_id;
        $not_allowed_tour->save();
        $data = [
            "status" => true,
            "message" => 'update sucessfully'
        ];
        return response()->json($data);
    }
    public function deleteFeatureTour(Request $request)
    {
        $tour_id = $request->query('tour_id');
        FeatureTour::where('tour_id', $tour_id)->delete();
        $data = [
            "status" => true,
            "message" => 'deleted sucessfully'
        ];
        return response()->json($data);
    }
    public function deleteNotAllowedTour(Request $request)
    {

        $tour_id = $request->query('tour_id');
        NotAllowedTour::where('tour_id', $tour_id)->delete();
        $data = [
            "status" => true,
            "message" => 'deleted sucessfully'
        ];
        return response()->json($data);
    }

    public function updateBulkTour(Request $request)
    {
        $id_array = $request->ids;
        $type = $request->type;
        // ['allowed','not_allowed','feature','not_feature'];
        foreach ($id_array as $id) {
            if ($type == 'feature') {
                $not_allowed_tour = NotAllowedTour::where('tour_id', $id)->first();
                if ($not_allowed_tour) {
                    $not_allowed_tour->delete();
                }
                $feature_tour = FeatureTour::where('tour_id', $id)->first();
                if (!$feature_tour) {
                    $feature_tour = new FeatureTour();
                    $feature_tour->tour_id = $id;
                    $feature_tour->save();
                }
            } else if ($type == 'not_feature') {
                $feature_tour = FeatureTour::where('tour_id', $id)->first();
                if ($feature_tour) {
                    $feature_tour->delete();
                }
            } else if ($type == 'not_allowed') {
                $feature_tour = FeatureTour::where('tour_id', $id)->first();
                if ($feature_tour) {
                    $feature_tour->delete();
                }
                $not_allowed_tour = NotAllowedTour::where('tour_id', $id)->first();
                if (!$not_allowed_tour) {
                    $not_allowed_tour = new NotAllowedTour();
                    $not_allowed_tour->tour_id = $id;
                    $not_allowed_tour->save();
                }
            } else if ($type == 'allowed') {
                $not_allowed_tour = NotAllowedTour::where('tour_id', $id)->first();
                if ($not_allowed_tour) {
                    $not_allowed_tour->delete();
                }
            } else {
                echo ('invalid type');
            }
        }
        $data = [
            "status" => true,
            "message" => 'update sucessfully'
        ];
        return response()->json($data);
    }
    public function showTableAndColumn(Request $request)
    {
        $search = $request->query('search');
        $all_data = [];

        // List of models corresponding to tables you're interested in
        $models = [
            'order_histories' => \App\Models\PetShop\OrderHistory::class, // Make sure to replace with the correct model paths
            'users' => \App\Models\PetShop\User::class,
        ];

        foreach ($models as $tableName => $modelClass) {
            // Check if the model uses the 'fillable' attribute
            $model = new $modelClass;
            $fillableFields = $model->getFillable();

            // Collect fillable fields and append table-column information
            foreach ($fillableFields as $field) {
                $all_data[] = $tableName . "-" . $field;
            }
        }

        // Fetch data from the secondary database
        // $secondaryDb = env('DB_DATABASE_RESOURCE');
        // $tables = DB::connection('mysql_resource_db')->select('SHOW TABLES');
        // $tableKey = 'Tables_in_' . $secondaryDb;

        // foreach ($tables as $table) {
        //     $tableName = $table->$tableKey;
        //     if ($tableName == "certificates") {
        //         $columns = Schema::connection('mysql_resource_db')->getColumnListing($tableName);
        //         foreach ($columns as $column) {
        //             $all_data[] = $tableName . "-" . $column;
        //         }
        //     }
        // }

        // Search functionality
        if (!empty($search)) {
            $search = strtolower($search);
            $filtered_data = array_filter($all_data, function ($item) use ($search) {
                return strpos(strtolower($item), $search) !== false;
            });
        } else {
            $filtered_data = $all_data;
        }
        $data = [
            "status" => true,
            "message" => 'data found',
            "data" => array_values($filtered_data)
        ];
        return array_values($filtered_data);
    }

    public function updateEmail(Request $request)
    {
        $id = $request->id;
        $subject = $request->subject;
        $email_body = $request->email_body;
        $email = Email::where('id', $id)->first();
        $email->subject = $subject;
        $email->body = $email_body;
        $email->save();
        $data = [
            "status" => true,
            "message" => 'update sucessfully'
        ];
        return response()->json($data);
    }

    public function deleteEmail(Request $request)
    {
        $id = $request->id;
        Email::where('id', $id)->delete();
        $data = [
            "status" => true,
            "message" => 'deleted sucessfully'
        ];
        return response()->json($data);
    }

    public function storeEmail(Request $request)
    {
        $title = $request->title;
        $subject = $request->subject;
        $email_body = $request->email_body;
        $email = new Email();
        $email->title = $title;
        $email->subject = $subject;
        $email->body = $email_body;
        $email->save();
        $data = [
            "status" => true,
            "message" => 'created sucessfully',
            "data" => $email
        ];
        return response()->json($data);
    }

    public function getEmail($id)
    {
        $email = Email::where('id', $id)->first();
        return $email;
    }
    public function getAllEmail()
    {
        $emails = Email::get();
        return $emails;
    }

    public function getPdf()
    {

        return view('Pdf.pdf');
    }

    public function updateChargeAndStatus(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'id' => 'required|integer', // Validate ID
            'charges' => 'required|numeric|min:0',
            'status' => 'required|in:on,off',
        ]);
        // dd($request->id);
        // Find the PaymentCharge by id
        $paymentCharges = PaymentCharge::findOrFail($validated['id']);

        // Update the charges and status
        $paymentCharges->update([
            'charges' => $validated['charges'],
            'status' => $validated['status'],
        ]);

        $data = [
            "status" => true,
            "message" => 'update sucessfully'
        ];
        return response()->json($data);
    }

    public function updatePopularityOfVenues(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'id' => 'required|integer|exists:payment_charges,id', // Validate ID
            'popularity_score' => 'required|numeric',
        ]);

        // Find the PaymentCharge by id
        $ticketVenue = TicketVenue::findOrFail($validated['id']);

        // Update the charges and status
        $ticketVenue->update([
            'popularity_score' => $validated['popularity_score'],
        ]);
        $data = [
            "status" => true,
            "message" => 'Ticket venue popularity updated Successfully'
        ];
        return response()->json($data);
    }
    public function getPaymentCharges()
    {
        $paymentCharges = PaymentCharge::select(['id', 'charges', 'status', 'type'])->get();
        return response()->json([
            "success" => true,
            'message' => 'Payment charges fetch successfully.',
            'payment_charge' => $paymentCharges
        ], 200);
    }

    public function getPaymentChargesByType($type)
    {
        $paymentCharges = PaymentCharge::select(['id', 'charges', 'status', 'type'])->where('type', $type)->get();
        return response()->json([
            "success" => true,
            'message' => 'Payment charges fetch successfully.',
            'payment_charge' => $paymentCharges
        ], 200);
    }

    public function updatePaymentCharges(Request $request)
    {
        $datas = $request->datas;
        $flag = true;
        foreach ($datas as $data) {
            $paymentCharges = PaymentCharge::select(['id', 'status', 'type', 'charges'])->where('type', $data['type'])->first();
            if ($paymentCharges) {
                $paymentCharges->status = $data['status'];
                $paymentCharges->charges = $data['charges'];
                $paymentCharges->save();
            } else {
                $flag = false;
            }
        }
        $paymentCharges = PaymentCharge::select(['id', 'status', 'type', 'charges'])->get();
        if ($flag) {

            return response()->json([
                "success" => true,
                'message' => 'updated sucessfully',
                'payment_charge' => $paymentCharges
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                'message' => 'invalid',
                'payment_charge' => false
            ], 200);
        }
    }
    public function getSetting()
    {
        $branding = Branding::get()->toArray();
        $details = [
            "stripe_key" => config('app.petshop_stripe_key'),
            "stripe_secret" => config('app.petshop_stripe_secret'),
            "petapikey" => config('app.petapikey'),
            "petid" => config('app.petid'),
            "peturl" => config('app.peturl'),
            "mail_mailer" => config('app.petshop_mail_mailer'),
            "mail_host" => config('app.petshop_mail_host'),
            "mail_port" => config('app.petshop_mail_port'),
            "mail_username" => config('app.petshop_mail_username'),
            "mail_password" => config('app.petshop_mail_password'),
            "mail_encryption" => config('app.petshop_mail_encryption'),
            "mail_from_address" => config('app.petshop_mail_from_address'),
            "mail_from_name" => config('app.petshop_mail_from_name'),
            "mail_support_address" => config('app.petshop_mail_support_address'),
            "mail_support_name" => config('app.petshop_mail_support_name'),

        ];
        $data = [
            "branding" => $branding,
            "details" => $details
        ];
        // dd($details);
        return $data;
    }


    public function getPetPoints()
    {
        $petPoint = PetPoint::select(['id', 'dollar', 'rate', 'status', 'purchase_limit'])->get();
        return response()->json([
            "success" => true,
            'message' => 'Pet Point fetch successfully.',
            'data' => $petPoint
        ], 200);
    }

    public function getPetPointsById($id)
    {
        $petPoint = PetPoint::select(['id', 'dollar', 'rate', 'status', 'purchase_limit'])->where('id', $id)->get();
        return response()->json([
            "success" => true,
            'message' => 'Pet Point fetch successfully.',
            'data' => $petPoint
        ], 200);
    }

    public function updatePetPoints(Request $request)
    {
        $id = $request->id;
        $dollar = $request->dollar;
        $rate = $request->rate;
        $purchase_limit = $request->purchase_limit;
        $status = $request->status;

        $petPoint = PetPoint::where('id', $id)->first();
        if ($petPoint) {
            $petPoint->dollar = $dollar;
            $petPoint->rate = $rate;
            $petPoint->purchase_limit = $purchase_limit;
            $petPoint->status = $status;
            $petPoint->save();
        }
        return response()->json([
            "success" => true,
            'message' => 'Pet Point fetch successfully.',
            'data' => $petPoint
        ], 200);
    }

    public function uploadUserPetPoints(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                'message' => 'Invalid Input'
            ], 200);
        }

        // Handle the uploaded file
        $file = $request->file('csv_file');
        $data = array_map('str_getcsv', file($file));

        // Skip the header row and insert data into the database
        foreach (array_slice($data, 1) as $row) {
            $user_pet_points = UserPetPoints::where('first_name', $row[0])->where('last_name', $row[1])->where('email', $row[2])->first();
            if ($user_pet_points) {
                $user_pet_points->pet_points = ($user_pet_points->pet_points + $row[3]);
                $user_pet_points->save();
            } else {
                $user_pet_points = new UserPetPoints();
                $user_pet_points->first_name = $row[0];
                $user_pet_points->last_name = $row[1];
                $user_pet_points->email = $row[2];
                $user_pet_points->pet_points = $row[3];
                $user_pet_points->save();
            }
        }
        return response()->json([
            "success" => true,
            'message' => 'Csv Uploaded Successfully.'
        ], 200);
    }

    public function getUserPetPoints()
    {

        $user_pet_points = UserPetPoints::get();

        return response()->json([
            "success" => true,
            'data' => $user_pet_points
        ], 200);
    }

    public function getUserPetPointsById($email)
    {

        $user_pet_points = UserPetPoints::where('email', $email)->first();

        return response()->json([
            "success" => true,
            'data' => $user_pet_points
        ], 200);
    }
}
