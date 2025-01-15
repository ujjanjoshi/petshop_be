<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Redeemer;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Http\Request;

class ShippingAddressController extends Controller
{
    public function shippingAddress(Request $request)
    {

        $redeemer_id = $request->user()->redeemer_id;

        $user_id = $redeemer_id;
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
        $shipping_address = new ShippingAddress();
        $shipping_address->user_id = $user_id;
        $shipping_address->name = $name;
        $shipping_address->address1 = $address1;
        if($request->address2!=null){
            $shipping_address->address2 = $address2;
        }

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
            "message" => 'shipping address saved sucessfully',
            "data" => $shipping_address
        ];
        return $data;
    }

    // public function addShippingAddress()
    // {
    //     $countries_controller = new CountryController();
    //     $get_countries = $countries_controller->getCountries();
    //     $data = [
    //         "countries" => $get_countries
    //     ];
    //     return view("Shipping.addShipping", compact('data'));
    // }

    public function getAllShippingAddress(Request $request)
    {

        $login_user = $request->user();
        $redeemer_user_data = Redeemer::with(['certificates'])->where('id', $login_user['redeemer_id'])->first();
        // $country_name = Country::where('id', intval($login_user['phone_country_code']))->select(['iso'])->first();
        $user_details = [
            "name" => $redeemer_user_data['first_name'] . " " . $redeemer_user_data['last_name'],
            "address1" => $redeemer_user_data['address'],
            "address2" => $redeemer_user_data['address2'],
            "city" => $redeemer_user_data['city'],
            "region" => $redeemer_user_data['state'],
            "country" => $redeemer_user_data['country'],
            "zip" => $redeemer_user_data['postal_code'],
            "phone" => $redeemer_user_data['phone'],
            "email" => $redeemer_user_data['email']
        ];
        $shipping_address = ShippingAddress::where('user_id', $login_user['redeemer_id'])->paginate(10);
        $data = [
            "status" => "success",
            "user_details" => $user_details,
            "shipping_address" => $shipping_address
        ];
        return $data;
    }


    public function getShippingAddress(Request $request, $id)
    {
        // dd($id);

        $login_user = $request->user();
        $shipping_address = ShippingAddress::where('user_id', $login_user['redeemer_id'])->where('id', intval($id))->first();
        $countries_controller = new CountryController();
        $get_countries = $countries_controller->getCountries();
        $data = [
            "status"=>"success",
            "countries" => $get_countries,
            "shipping_address" => $shipping_address
        ];
        return $data;
    }

    public function saveExistingShippingAddress(Request $request, $id)
    {
        // dd($id);
        $login_user = $request->user();
        $shipping_address = ShippingAddress::where('user_id',  $login_user['redeemer_id'])->where('id', intval($id))->first();
        $data = [
            "status" => "success",
            "message"=>"found",
            "data" => $shipping_address,
        ];
        return $data;
    }

    public function updateExistingShippingAddress(Request $request, $id)
    {
        $login_user = $request->user();
        $user_id = $login_user['redeemer_id'];
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
        $shipping_address = ShippingAddress::find($id);

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
            "message"=>"shipping address saved sucessfully",
            "data" => $shipping_address,
        ];
        return $data;
    }

    public function deleteShippingAddress($id)
    {

        ShippingAddress::where('id', $id)->delete();
        $data = [
            "status" => "success",
            "message"=>"shipping address delete saved sucessfully",
            "data" => null,
        ];
        return $data;
    }
}
