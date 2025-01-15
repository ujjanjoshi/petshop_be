<?php

namespace App\Http\Controllers;

use App\Jobs\SendNotificationMailJob;
use App\Jobs\SendOTPMailJobs;
use App\Jobs\SendVerificationMailJobs;
use App\Mail\SendNotificationMail;
use App\Models\Certificate;
use App\Models\GiftCard;
use Illuminate\Support\Str;
use App\Models\Redeemer;
use App\Models\User;
use App\Services\CertificateService;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CertificateController extends Controller
{
    public function showCertificateDetails(Request $request)
    {
        $data = [];

        $redeemer = $request->user()->redeemer ?? new Redeemer;
        foreach ($redeemer->certificates as $certificate) {
            $data[] = [
                "name" => $certificate->name,
                "type" => $this->certificateType($certificate['sku']),
                "certificate_code" => $certificate['code'],
                "price" => $certificate->price,
                "date" => $certificate->created_at->format('Y-m-d'),
                "travel_date" => $certificate->travel_date,
            ];
        }
        return ($data);
    }

    public function certificateType($type)
    {
        if ($type == "SHOPMERCH") {
            return "Merchandise";
        } else if ($type == "SHOPTICKET") {
            return "Ticket";
        } else if ($type == "SHOPHOTEL") {
            return "Hotel";
        } else if (Str::contains(strtoupper($type), 'RENT')) {
            return "Rent";
        } else {
            return "Experience";
        }
    }

    public function updateTravelDate(Request $request, $code)
    {
        $certificate = Certificate::where('code', $code)->first();

        $certificate->start_date = $request->start_date;
        $certificate->end_date = $request->end_date;

        $srv = new CertificateService;
        return $srv->updateTravelDate($certificate);
    }
    public function getCertificatePdf($code)
    {
        $certificate = Certificate::where('code', $code)->first();
        $srv = new CertificateService;
        $pdf = $srv->getRedemption($certificate);

//        'attachment'
        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' =>  'inline; filename="' . $code . '.pdf"',
            'Content-Length' => strlen($pdf),
        ]);
    }

    public function sendOTPMail(Request $request, $code)
    {
        $redeemer = $redeemer = $request->user()->redeemer;

        $certificate = GiftCard::firstWhere('code',  $code);
        if ($certificate == null) {
            return '';
        }
        $user_controller = new UserController();
        $email_template = $user_controller->emailTemplate(4, $redeemer->email);
        // $data = [
        //     'email' => $redeemer->email,
        //     'subject' => $email_template['subject'],
        //     'body' => $email_template['body']
        // ];
        $otp = mt_rand(1000, 9999);
        $user= $request->user();
        $user->otp_code=$otp;
        $user->otp_send_at=new DateTime();
        $user->save();
        $data = [
            'first_name' =>  $request->first_name,
            'last_name'  =>  $request->last_name,
            'email' =>  $request->email,
            'phone' =>  $request->phone,
            'subject' => $email_template['subject'],
            'body' => $email_template['body'],
            'actual_email' => $redeemer->email,
            'transfer_email' =>  $request->email,
            'otp'=>$otp
        ];
        dispatch(new SendOTPMailJobs($data));
        $data['certificate_code'] = $code;
        return $data;
    }

    public function transferCertificate(Request $request)
    {
        // dd($data);
        $user=$redeemer = $request->user();
        $otp_send_at= new DateTime($user->otp_send_at);
        $current_date_time=new DateTime(); // Current time
        $interval =$otp_send_at->diff($current_date_time);
        $user_controller = new UserController();
        if($interval->h >=2){
           
            $email_template = $user_controller->emailTemplate(4, $redeemer->email);
            $data = [
                'first_name' =>  $request->first_name,
                'last_name'  =>  $request->last_name,
                'email' =>  $request->email,
                'phone' =>  $request->phone,
                'subject' => $email_template['subject'],
                'body' => $email_template['body'],
                'actual_email' => $redeemer->email,
                'transfer_email' =>  $request->email
            ];
            dispatch(new SendOTPMailJobs($data));

            $otp = mt_rand(1000, 9999);
            $user=$request->user();
            $user->otp_code=$otp;
            $user->otp_send_at=new DateTime();
            $user->save();
            $response = [
                "status" => "success",
                "message" => "OTP resend to the email"
            ];
            return $response;
        }
        if ($request->otp == $user->otp_code && $interval->h < 2 ) {
            $srv = new CertificateService;
            $certificate = GiftCard::firstWhere('code',  $request->certificate_code);
            if ($certificate == null) {
                return '';
            }
            $transfer_result = $srv->transfer($certificate, [
                'first_name' =>  $request->first_name,
                'last_name'  =>  $request->last_name,
                'email' =>  $request->email,
                'phone' =>  $request->phone,

            ]);
            // dd($transfer_result);
            if ($transfer_result->status == "success") {
                //  'first_name', 'middle_name', 'last_name',
                // 'address', 'address2', 'city', 'state', 'country', 'zip', 'phone', 'mobile', 'email', 'vip'
                $redeemer = Redeemer::with(['user'])->where('email', $request->email)->first();
                // dd($redeemer);
                if ($redeemer) {
                    // dd($redeemer->user);
                    if ($redeemer->user == null) {
                        $user = new User();
                        $user->redeemer_id = $redeemer->id;
                        $user->status = $request->status ?? 0;
                        $user->password = "Abc@123";
                        $user->email_send_datetime = new DateTime();
                        $user->is_approve = true;
                        $user->save();
                        $email_template = $user_controller->emailTemplate(1, $redeemer->email);
                        $data = [
                            'subject' => $email_template['subject'],
                            'body' => $email_template['body'],
                            'email' => $redeemer->email
                        ];
                        dispatch(new SendVerificationMailJobs($data));
                        $response = [
                            "status" => "success",
                            "message" => "Sucessfully Transfer"
                        ];
                        return $response;
                    } else {
                        $email_template = $user_controller->emailTemplate(5, $redeemer->email);
                        $data = [
                            'subject' => $email_template['subject'],
                            'body' => $email_template['body'],
                            'email' => $redeemer->email
                        ];
                        dispatch(new SendNotificationMailJob($data));
                        $response = [
                            "status" => "success",
                            "message" => "Sucessfully Send Notification"
                        ];
                        return $response;;
                    }
                } else {
                    $redeemer = new Redeemer();
                    $redeemer->first_name =  $request->first_name;
                    $redeemer->last_name =  $request->last_name;
                    $redeemer->email =  $request->email;
                    $redeemer->phone =  $request->phone;
                    $redeemer->save();
                    $user = new User();
                    $user->redeemer_id = $redeemer->id;
                    $user->status = $request->status ?? 0;
                    $user->email_send_datetime = new DateTime();
                    $user->password = "Abc@123";
                    $user->is_approve = true;
                    $user->save();
                    $user_controller = new UserController();
                    $email_template = $user_controller->emailTemplate(1, $redeemer->email);
                    $data = [
                        'subject' => $email_template['subject'],
                        'body' => $email_template['body'],
                        'email' => $redeemer->email
                    ];
                    dispatch(new SendVerificationMailJobs($data));
                    $response = [
                        "status" => "success",
                        "message" => "Sucessfully Transfer"
                    ];
                    return $response;
                }
            }
        } else {
            $response = [
                "status" => "false",
                "message" => "OTP Didnot Match"
            ];
            return $response;
        }
    }
}
