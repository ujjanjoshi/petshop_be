<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Services\CertificateService;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    // Certificate $certificate, array $guest


    // here is the json for the guest


    // {

    //     "id": "1",
    //     "age": null,
    //     "dob": null,
    //     "note": "testing",
    //     "email": null,
    //     "gender": null,
    //     "mobile": "703-786-8022",
    //     "guest_id": 1,
    //     "last_name": "Zhong",
    //     "first_name": "Joe",
    //     "middle_name": null,
    //     "nationality": null
    //     "passport_id": null,
    //     "passport_expiration": null
    // }

    public function addGuest(Request $request,$code)
    {
        $certificate_code= $code;
        $certificate= Certificate::where('code',$certificate_code)->first();
        $age = $request->age;
        $dob = $request->dob;
        $note = $request->note;
        $email = $request->email;
        $gender = $request->gender;
        $mobile = $request->mobile;
        $guest_id = $request->guest_id;
        $last_name = $request->last_name;
        $first_name = $request->first_name;
        $middle_name = $request->middle_name;
        $nationality = $request->nationality;
        $passport_id = $request->passport_id;
        $passport_expiration = $request->passport_expiration;
        $is_primary= $request->is_primary;
        $data = [
            "age" => $age,
            "dob" => $dob,
            "note" => $note,
            "email" => $email,
            "gender" => $gender,
            "mobile" => $mobile,
            "guest_id" => $guest_id,
            "last_name" => $last_name,
            "first_name" => $first_name,
            "middle_name" => $middle_name,
            "nationality" => $nationality,
            "passport_id" => $passport_id,
            "passport_expiration" => $passport_expiration,
            "is_primary"=>$is_primary,
        ];
        $certificateService= new CertificateService;
        return $certificateService->addGuest($certificate,$data);
    }


    public function getGuest($code) {
        $certificate_code= $code;
        $certificate= Certificate::with(['redeemer'])->where('code',$certificate_code)->first();
        // dd($certificate->toArray());
        $certificateService= new CertificateService;
        $responses= $certificateService->listGuest($certificate);
        $certificate_controller= new CertificateController();
        $certificate->type=$certificate_controller->certificateType($certificate['sku']);
        $responses->info=$certificate;
        
        return $responses;
    }
    public function  updateGuest(Request $request, $code,$id) {

        $certificate_code= $code;
        $certificate= Certificate::where('code',$certificate_code)->first();
        $age = $request->age;
        $dob = $request->dob;
        $note = $request->note;
        $email = $request->email;
        $gender = $request->gender;
        $mobile = $request->mobile;
        $last_name = $request->last_name;
        $first_name = $request->first_name;
        $middle_name = $request->middle_name;
        $nationality = $request->nationality;
        $passport_id = $request->passport_id;
        $passport_expiration = $request->passport_expiration;
        $is_primary= $request->is_primary;
        $data = [
            "age" => $age,
            "dob" => $dob,
            "note" => $note,
            "email" => $email,
            "gender" => $gender,
            "mobile" => $mobile,
            "last_name" => $last_name,
            "first_name" => $first_name,
            "middle_name" => $middle_name,
            "nationality" => $nationality,
            "passport_id" => $passport_id,
            "passport_expiration" => $passport_expiration,
            "is_primary"=>$is_primary,
        ];
        $certificateService= new CertificateService;
        return $certificateService->updateGuest($certificate,$id,$data);
    }
    public function deleteGuest($code,$id) {
        $certificate_code= $code;
        $certificate= Certificate::where('code',$certificate_code)->first();
        $certificateService= new CertificateService;
        return $certificateService->deleteGuest($certificate,$id);
    }
}
