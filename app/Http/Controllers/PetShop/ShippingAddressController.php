<?php

namespace App\Http\Controllers\PetShop;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CountryController;
use App\Models\Country;
use App\Models\PetShop\User;
use App\Models\PetShop\ShippingAddress as ModelsShippingAddress;
use Illuminate\Http\Request;

class ShippingAddressController extends Controller
{
    public function shippingAddress(Request $request)
    {
        $user_id = $request->user()->user_id;
        $user = User::where('user_id', $user_id)->first();
        if ($user) {
            $login_user = $request->user();
            $user_id = $login_user['user_id'];
            $name = $request->name;
            $address1 = $request->address1;
            $address2 = $request->address2;
            $city = $request->city;
            $region = $request->region;
            $country = $request->country;
            $zip = $request->zip;
            $phone = $request->phone;
            $email = $request->email;
            $is_gifted = $request->is_gifted;
            $fromName = $request->fromName;
            $fromEmail = $request->fromEmail;
            $message = $request->message;
            $shipping_address = new ModelsShippingAddress();
            $shipping_address->user_id = $user_id;
            $shipping_address->name = $name;
            $shipping_address->address1 = $address1;
            $shipping_address->address2 = $address2;
            $shipping_address->city = $city;
            $shipping_address->region = $region;
            $shipping_address->country = $country;
            $shipping_address->zip = $zip;
            $shipping_address->phone = $phone;
            $shipping_address->email = $email;
            $shipping_address->is_gifted = $is_gifted;
            $shipping_address->fromName = $fromName;
            $shipping_address->fromEmail = $fromEmail;
            $shipping_address->message = $message;
            $shipping_address->save();
            $data = [
                "status" => "success",
                "message" => "shipping address saved sucessfully",
                "data" => $shipping_address
            ];
            return response()->json($data);
        }
    }

    public function getAllShippingAddress(Request $request)
    {
        $user_id = $request->user()->user_id;
        $user = User::where('user_id', $user_id)->first();
        if ($user) {
            $login_user = $request->user();
            $country_name = Country::where('phonecode', intval($login_user['phone_country_code']))->select(['iso'])->first();
            
            $user_details = [
                "name" => $login_user['first_name'] . " " . $login_user['last_name'],
                "address1" => $login_user['address1'],
                "address2" => $login_user['address2'],
                "city" => $login_user['city'],
                "region" => $login_user['state'],
                "country" => $country_name['iso'],
                "zip" => $login_user['postal_code'],
                "phone" => $login_user['phone'],
                "email" => $login_user['email']
            ];
            $shipping_address = ModelsShippingAddress::where('user_id', $login_user['user_id'])->paginate(10);
            $data = [
                "status" => "success",
                "message" => "Found",
                "data" => [
                    "shipping_address" => $shipping_address,
                    "user_shipping" => $user_details
                ]
            ];
            return response()->json($data);
        }
    }


    public function getShippingAddress(Request $request, $id)
    {
        // dd($id);
        $user_id = $request->user()->user_id;
        $user = User::where('user_id', $user_id)->first();
        if ($user) {
            $login_user = $request->user();
            $shipping_address = ModelsShippingAddress::where('user_id', $login_user['user_id'])->where('id', intval($id))->first();
            $countries_controller = new CountryController();
            $get_countries = $countries_controller->getCountries();
            $data = [
                "countries" => $get_countries,
                "shipping_address" => $shipping_address
            ];
            return response()->json($data);
            // return view('Shipping.editShipping', compact('shipping_address'), compact('data'));
        }
    }

    public function saveExistingShippingAddress(Request $request, $id)
    {
        // dd($id);
        $user_id = $request->user()->user_id;
        $user = User::where('user_id', $user_id)->first();
        if ($user) {
            $login_user = $request->user();
            $shipping_address = ModelsShippingAddress::where('user_id', $login_user['user_id'])->where('id', intval($id))->first();
            // dd($shipping_address);
            return $shipping_address;
        }
    }

    public function updateExistingShippingAddress(Request $request, $id)
    {
        $user_id = $request->user()->user_id;
        $user = User::where('user_id', $user_id)->first();
        if ($user) {
            $login_user = $request->user();
            $user_id = $login_user['user_id'];
            $name = $request->name;
            $address1 = $request->address1;
            $address2 = $request->address2;
            $city = $request->city;
            $region = $request->region;
            $country = $request->country;
            $zip = $request->zip;
            $phone = $request->phone;
            $email = $request->email;
            $is_gifted = $request->is_gifted;
            $fromName = $request->fromName;
            $fromEmail = $request->fromEmail;
            $message = $request->message;
            $shipping_address = ModelsShippingAddress::find($id);

            if ($shipping_address) {
                $shipping_address->user_id = $user_id;
                $shipping_address->name = $name;
                $shipping_address->address1 = $address1;
                $shipping_address->address2 = $address2;
                $shipping_address->city = $city;
                $shipping_address->region = $region;
                $shipping_address->country = $country;
                $shipping_address->zip = $zip;
                $shipping_address->phone = $phone;
                $shipping_address->email = $email;
                $shipping_address->is_gifted = $is_gifted;
                $shipping_address->fromName = $fromName;
                $shipping_address->fromEmail = $fromEmail;
                $shipping_address->message = $message;
                $shipping_address->save();
            } else {
                // Handle the case where the address with the specified ID does not exist
                // For example, you could throw an exception or return an error message
                echo "Shipping address not found.";
            }
            $data = [
                "status" => "success",
                "message" => "shipping address saved sucessfully",
            ];
            return response()->json($data);
        }
    }

    public function deleteShippingAddress(Request $request, $id)
    {
        ModelsShippingAddress::where('id', $id)->delete();
        $data = [
            "status" => "success",
            "message"=>"shipping address delete saved sucessfully",
            "data" => null,
        ];
        return $data;
    }
    // public function shippingAddress(Request $request)
    // {
    //     $user_id = "661665e951d65";
    //     $name = "John Doe";
    //     $address1 = "456 Elm Street";
    //     $address2 = "Apt 12";
    //     $city = "Springfield";
    //     $region = "IL";
    //     $country = "USA";
    //     $zip = "62704";
    //     $phone = "3125557890";  // US phone numbers are typically in the format +1 (area code) XXX-XXXX.
    //     $email = "john.doe@example.com";
    //     $is_gifted = true;
    //     $fromName = "Jane Smith";
    //     $fromEmail = "jane.smith@example.com";
    //     $message = "Happy Holidays! Enjoy your gift!";
    //     $shipping_address = new ModelsShippingAddress();
    //     $shipping_address->user_id = $user_id;
    //     $shipping_address->name = $name;
    //     $shipping_address->address1 = $address1;
    //     $shipping_address->address2 = $address2;
    //     $shipping_address->city = $city;
    //     $shipping_address->region = $region;
    //     $shipping_address->country = $country;
    //     $shipping_address->zip = $zip;
    //     $shipping_address->phone = $phone;
    //     $shipping_address->email = $email;
    //     $shipping_address->is_gifted = $is_gifted;
    //     $shipping_address->fromName = $fromName;
    //     $shipping_address->fromEmail = $fromEmail;
    //     $shipping_address->message = $message;
    //     $shipping_address->save();
    //     Session::put('shipping_address', $shipping_address);
    //     return ('shipping address saved sucessfully');
    // }
    // public function token(){
    //     return response()->json(['csrf_token' => csrf_token()]);
    // }
}
