<?php

namespace App\Http\Controllers;

use App\Jobs\SendApproveMailJobs;
use App\Jobs\SendForgetPasswordMailJobs;
use App\Models\Certificate;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Email;
use App\Models\Redeemer;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //     use AuthenticatesUsers;

    //     /**
    //      * Create a new controller instance.
    //      *
    //      * @return void
    //      */
    //     public function __construct()
    //     {
    //         $this->middleware('guest')->except('logout');
    //     }
    /** 
     * Register api 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function signup(Request $request)
    {
        // // VALIDATION - Laravel provides validation rules in form request or controller method directly
        $validatedData = Validator::make($request->all(), [
            'redeemer_id' => 'required',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',        // must be at least 1 lowercase
                'regex:/[A-Z]/',        // must be at least 1 uppercase
                'regex:/[0-9]/',        // must be at least 1 uppercase
                'regex:/[!@#$%^&*]/',
                'confirmed' // must be at least 1 special character
            ],
        ]);
        if ($validatedData->fails()) {
            return response()->json(['status' => 'error', 'message' => $validatedData->errors(), 'data' => null], 400);
        }

        $redeemer = Redeemer::find($request->redeemer_id);
        if ($redeemer == null) {
            $response = [
                "status" => false,
                "message" => "Oops, something wrong. Cannot find the required Redeemer Information",
                "data" => null
            ];
            return response()->json($response);
        }
        /**
         * Need the email/certificate_code from earlier step
         */
        $password = password_hash($request->password, PASSWORD_DEFAULT);

        if ($request->email == null || $request->email == $redeemer->email) {
            //
            // the email is the redeemer's email
            // claim its own (ie, redeemer) account, verifying the email
            //
            $user = $redeemer->user;
            if (!$user) {

                $user = new User();
                $user->redeemer_id =  $redeemer->id;

                $user->password = $password;
                $user->status = $request->status ?? 0;
                $user->email_send_datetime = new DateTime();
                $user->is_approve = false;
                $user->save();

                // email verification
                $email_template = $this->emailTemplate(3, $redeemer->email);
                $data = [
                    'email' => $redeemer->email,
                    'subject' => $email_template['subject'],
                    'body' => $email_template['body'],
                    'base_url'=>$request->base_url
                ];
                dispatch(new SendApproveMailJobs($data));

                // RETURN RESPONSE
                $response = [
                    "status" => "success",
                    "message" => "Account registered successfully.",
                    "data" => $user,
                ];
                return response()->json($response);
            } else {
                $response = [
                    "status" => false,
                    "message" => "Account Already Exist! Proceed to Login",
                    "data" => null
                ];
                return response()->json($response);
            }
        } else {
            //
            // the email is not the redeemer's email 
            // take over the certificate/giftcard from the owner, approve ??
            //
            $redeemer2 = Redeemer::firstWhere('email', $request->email);
            if ($redeemer2 == null) {
                //
                // TODO verify this email too ??
                // 
                $redeemer2 = new Redeemer;
                $redeemer2->email = $request->email;

                $srv = new \App\Services\RedeemerService;
                $redeemer2 = $srv->create($redeemer2);
            }
            if ($redeemer2 === false) {
                $response = [
                    "status" => false,
                    "message" => "Oops, error enabling your account. Please contact the support",
                    "data" => null
                ];
                return response()->json($response);
            }

            if ($redeemer2->user == null) {

                $user = new User();
                $user->redeemer_id =  $redeemer2->id;

                $user->password = $password;
                $user->status = $request->status ?? 0;
                $user->email_send_datetime = new DateTime();
                $user->is_approve = false;
                $user->save();

                // email approval
                $email_template = $this->emailTemplate(3, $redeemer->email);
                $data = [
                    'email' => $redeemer->email,
                    'subject' => $email_template['subject'],
                    'body' => $email_template['body'],
                    'base_url'=>$request->base_url
                ];
                dispatch(new SendApproveMailJobs($data));

                // RETURN RESPONSE
                $response = [
                    "status" => "success",
                    "message" => "Account registered successfully.",
                    "data" => $user,
                ];
                return response()->json($response);
            } else {
                $response = [
                    "status" => false,
                    "message" => "Account Already Exist! Proceed to Login",
                    "data" => null
                ];
                return response()->json($response);
            }
        }
    }

    public function activateAccount($email)
    {
        $decryptedEmail = decrypt($email);

        $user = User::where('email', $decryptedEmail)
            ->where('is_approve', true)
            ->first();
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $user->email_send_datetime);
        // Check if the conversion was successful
        if ($date === false) {
            echo "Invalid date format.";
            exit;
        }

        // Get current date and time
        $currentDate = new DateTime();
        // Calculate the difference in hours
        $interval = $currentDate->diff($date);
        $hoursDifference = $interval->h + ($interval->days * 24);
        \Log::info("activateAccount: $decryptedEmail ==> hoursDiff=" . $hoursDifference);

        // Check if the difference is within 2 hours
        if ($hoursDifference < 2) {
            $user->update(['status' => 1, "is_verified" => true]);
            $data = [
                "status" => "success",
                "message" => "Account Activated Procced to Login!",
                "data" => null
            ];
            return response()->json($data);
        } else {
            $data = ["status" => "error", "message" => "Link Expired!!", "data" => null];
            return response()->json($data);
        }
    }


    public function login(Request $request)
    {
        // // VALIDATION
        // $validatedData = request()->validate([
        //     'email' => 'required|email',
        //     'password' => 'required',
        // ]);

        // SELECT USER FROM DATABASE
        // dd(md5($request->password));
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => [
                'required',
                'string'
            ],
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors(), 'data' => null], 400);
        }
        $redeemer = Redeemer::where('email', $request->email)->first();
        $user = User::where('redeemer_id', $redeemer->id)
            // ->where('password', md5($request->password))
            ->first();
            // dd(password_verify($request->password, $user->password));
        if ($user) {
            if (password_verify($request->password, $user->password)) {
                $date = DateTime::createFromFormat('Y-m-d H:i:s', $user->email_send_datetime);

                // Check if the conversion was successful
                if ($date === false) {
                    echo "Invalid date format.";
                    exit;
                }

                // Get current date and time
                $currentDate = new DateTime();
                // Calculate the difference in hours
                $interval = $currentDate->diff($date);
                $hoursDifference = $interval->h + ($interval->days * 24);
                // dd($hoursDifference);
                // Check if the difference is within 2 hours
                if ($hoursDifference >= 2 && !$user['is_approve']) {
                    $email_template = $this->emailTemplate(3, $redeemer->email);
                    $data = [
                        'email' => $request->email,
                        'subject' => $email_template['subject'],
                        'body' => $email_template['body'],
                        'base_url'=>$request->base_url
                    ];
                    dispatch(new SendApproveMailJobs($data));
                    $user->email_send_datetime = new DateTime();
                    $user->save();
                    $data = ["status" => "error", "message" => "Mail ReSend For Approved", "data" => $user];
                    return response()->json($data);
                }
                if ($user->status == 0 && !$user['is_approve']) {
                    $data = ["status" => "error", "message" => "Account Not Approved By Owner", "data" => $user];
                    return response()->json($data);
                }

                $token = $user->createToken('UserToken')->plainTextToken;
                $data = [
                    "status" => "success",
                    "token" => $token,
                    "user" => $redeemer
                ];
                return response()->json($data);
            } else {
                $data = ["status" => "error", "message" => "Email or Password Incorrect", "data" => null];
                return response()->json($data);
            }
        } else {
            $data = ["status" => "error", "message" => "User Not Found", "data" => null];
            return response()->json($data);
            // return (["status" => false, "message" => "no user found", "data" => null]);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();
        return response()->json([
            'status' => true,
            'message' => 'User Logout Successfully',
            'data' => null
        ], 200);
    }

    public function updateUserData(Request $request)
    {
        $redeemer = $request->user()->redeemer;
        if ($redeemer) {
            $redeemer->fill($request->all());
            if ($redeemer->phone !== null) {
                $redeemer->phone = str_replace(' ', '', $redeemer->phone);
            }
            if ($redeemer->mobile !== null) {
                $redeemer->mobile = str_replace(' ', '', $redeemer->mobile);
            }

            $srv = new \App\Services\RedeemerService;
            $srv->update($redeemer);

            $countries = Country::getList();
            $data = [
                "user_info" => $redeemer,
                "countries" => $countries
            ];
            return response()->json($data);
        }
    }


    public function changePassword(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $old_password = $request->old_password;
            if (!password_verify($old_password, $user->password)) {
                $data = ["status" => "error", "message" => "Invalid Old Password", "data" => null];
                return response()->json($data);
            }

            $messages = [
                'new_password.regex' => 'Password must contain at least one digit, one special character and both upper & lowercase letters.',
            ];

            $new_password = $request->new_password;

            $password = password_hash($new_password, PASSWORD_DEFAULT);
            $user->password = $password;
            $user->save();
            $data = [
                "status" => "success",
                "message" => "Password Change Sucessfully!",
                "data" => null
            ];
            return response()->json($data);
        }
    }

    // public function getResetPasswordLink()
    // {
    //     return view('Accounts.resetPasswordLink');
    // }
    public function sendResetPasswordLink(Request $request)
    {
        $email = $request->input('email');
        $user = Redeemer::userEmail($email);
        if ($user) {
            $email_template = $this->emailTemplate(2, $email);
            $data = [
                'email' => $request->email,
                'subject' => $email_template['subject'],
                'body' => $email_template['body'],
                'base_url'=>$request->base_url
            ];
            dispatch(new SendForgetPasswordMailJobs($data));

            $user->email_send_datetime = new DateTime();
            $user->save();

            $email_message = ["status" => "success", "message" => "Reset Password Email Sent", "data" => $user];
        } else {
            $email_message = ["status" => "error", "message" => "Unknown user", "data" => ''];
        }
        return response()->json($email_message);
    }

    public function resetPasswordForm($email)
    {
        $decryptedEmail = decrypt($email);
        $user = Redeemer::userEmail($decryptedEmail);
        if ($user == null) {
            Log::error("No redeemer found on email=" . $decryptedEmail);

            echo "Oops, someting wrong, please make sure the link is correct!";
            return false;
        }

        $date = DateTime::createFromFormat('Y-m-d H:i:s', $user->email_send_datetime);
        if ($date === false) {
            echo "Invalid date format.";
            exit;
        }

        // Get current date and time
        $currentDate = new DateTime();
        // Calculate the difference in hours
        $interval = $currentDate->diff($date);
        $hoursDifference = $interval->h + ($interval->days * 24);
        // dd($hoursDifference);
        // Check if the difference is within 2 hours
        if ($hoursDifference < 2 /* && $user->email_send_datetime > $user->updated_at */) {
            return true;
        } else {
            return false;
        }
    }
    public function changeResetPassword(Request $request)
    {
        $new_password = $request->new_password;
        $email = $request->email;
        $check_link = $this->resetPasswordForm($email);
        if ($check_link) {
            $decryptedEmail = decrypt($request->email);
            $user = Redeemer::userEmail($decryptedEmail);
            $password = null;
            if ($user) {

                $messages = [
                    'new_password.regex' => 'Password must contain at least one digit, one special character and both upper & lowercase letters.',
                ];
                $validator = Validator::make($request->all(), [
                    'new_password' => [
                        'required',
                        'string',
                        'min:8',
                        'regex:/[a-z]/',        // must be at least 1 lowercase
                        'regex:/[A-Z]/',        // must be at least 1 uppercase
                        'regex:/[0-9]/',        // must be at least 1 uppercase
                        'regex:/[!@#$%^&*]/',   // must be at least 1 special character
                        'confirmed'
                    ],
                ], $messages)->validate();
                $new_password = $request->new_password;
                $password = password_hash($new_password, PASSWORD_DEFAULT);
                $user->password = $password;
                $user->save();
            }
            $data = [
                "status" => "success",
                "message" => "Password Change Successfully!",
                "data" => null
            ];
            return response()->json($data);
        } else {
            $data = ["status" => "error", "message" => "Link Expired!! Please request for new link", "data" => null];
            return response()->json($data);
        }
    }

    public function getUserData(Request $request)
    {
        $get_countries = Country::getList();
        $redeemer = $request->user()->redeemer;
        $data = [
            "countries" => $get_countries,
            "user" => $redeemer,
        ];
        return response()->json($data);
    }

    public function checkExist(Request $request)
    {
        $certificate_code = $request->certificate_code;
        $email = $request->email;
        if ($certificate_code != null && $email != null) {
            $certificate = Certificate::firstWhere('code', $certificate_code);
            if ($certificate == null) {
                $data = [
                    "status" => "error",
                    "message" => "Unknown Certificate or JustRewards Email Address",
                    "data" => null,
                    "redeemer_id" => null,
                    "certificate_code" => null,
                    "email" => null
                ];
                return response()->json($data);
            }
            //
            // use the certificate email for approval of the taking over
            // email verification on the entered email.
            //
            if (empty($certificate->email)) {
                $data = [
                    "status" => "error",
                    "message" => "Unknown Certificate or JustRewards Email Address",
                    "data" => null,
                    "redeemer_id" => null,
                    "certificate_code" => null,
                    "email" => null
                ];
                return response()->json($data);
            }
            $data = [
                "status" => "success",
                "message" => "Valid",
                "data" => null,
                "redeemer_id" => $certificate->redeemer_id,
                "certificate_code" => $certificate_code,
                "email" => $email
            ];
            return response()->json($data);
        } else if ($certificate_code == null && $email != null) {
            $redeemer = Redeemer::where('email', $email)->first();
            if ($redeemer) {
                $data = [
                    "status" => "success",
                    "message" => "Valid",
                    "data" => null,
                    "redeemer_id" => $redeemer->id,
                    "certificate_code" => $certificate_code,
                    "email" => $email
                ];
                return response()->json($data);
            } else {
                $data = [
                    "status" => "error",
                    "message" => "Unknown JustRewards Email Address",
                    "data" => null,
                    "redeemer_id" => null,
                    "certificate_code" => null,
                    "email" => null
                ];
                return response()->json($data);
            }
        } else {
            $data = [
                "status" => "error",
                "message" => "Invalid data",
                "data" => null,
                "redeemer_id" => null,
                "certificate_code" => null,
                "email" => null
            ];
            return response()->json($data);
        }
    }

    public function sendActivationMail($email)
    {
        $decryptedEmail = decrypt($email);
        $user = Redeemer::userEmail($decryptedEmail);

        if ($user == null) {
            Log::error("No redeemer found on email=" . $decryptedEmail);

            echo "Oops, someting wrong, please make sure the link is correct!";
            return false;
        }

        $date = DateTime::createFromFormat('Y-m-d H:i:s', $user->email_send_datetime);
        // Check if the conversion was successful
        if ($date === false) {
            echo "Invalid date format.";
            exit;
        }

        // Get current date and time
        $currentDate = new DateTime();
        // Calculate the difference in hours
        $interval = $currentDate->diff($date);
        $hoursDifference = $interval->h + ($interval->days * 24);
        // dd($hoursDifference);
        // Check if the difference is within 2 hours
        if ($hoursDifference < 2) {
            $user->status = true;
            $user->is_approve = true;
            $user->save();
            $data = [
                "status" => "success",
                "message" => "Account Activated Procced to Login!",
                "data" => null
            ];
            return response()->json($data);
        } else {
            $data = ["status" => "error", "message" => "Link Expired!!", "data" => null];
            return response()->json($data);
        }
    }
    public function encryptedKey(Request $request)
    {
        // Ensure key length is correct for AES-256
        $key = substr(config('app.key'), 7, 32); // 32 bytes for AES-256

        // Generate a random IV if not using a static one
        $iv = random_bytes(16); // 16 bytes for IV

        // Encrypt data
        $stripe_key_encrypted = openssl_encrypt(config('app.stripe_key'), 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        $stripe_secret_encrypted = openssl_encrypt(config('app.stripe_secret'), 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);


        // Encode values to Base64
        $details = [
            'key' => base64_encode($key),
            'iv' => base64_encode($iv),
            'stripe_key' => base64_encode($stripe_key_encrypted),
            'stripe_secret' => base64_encode($stripe_secret_encrypted),

        ];

        return $details;
    }

    public function emailTemplate($id, $user_email)
    {
        $user_id = Redeemer::where('email', $user_email)->pluck('id')
            ->first();
        $email = Email::where("id", $id)->select(['subject', 'body'])->first();
        preg_match_all('/\{(.*?)\}/', $email->subject, $matches);
        $subject_array = $matches[0];
        $result = [];
        $subject_array_string = $matches[1];
        foreach ($subject_array as $index => $placeholder) {
            $value = explode('-', $subject_array_string[$index]);
            if (count($value) == 2) {
                $table = $value[0];
                $column = $value[1];
                try {
                    // Try the first query with the original connection
                    $result = DB::connection('mysql_petlink')->table($table)
                        ->where('user_id', $user_id)
                        ->pluck($column)
                        ->first();

                    if ($result !== null) {
                        $email->subject = str_replace($placeholder, $result, $email->subject);
                    }
                } catch (\Exception $e) {
                    Log::error("Error connecting to mysql_petlink: " . $e->getMessage());

                    // Optionally, try another connection if the first one fails
                    try {
                        $result = DB::table($table)
                            ->where('user_id', $user_id)
                            ->pluck($column)
                            ->first();

                        if ($result !== null) {
                            $email->subject = str_replace($placeholder, $result, $email->subject);
                        }
                    } catch (\Exception $e) {
                        Log::error("Error connecting to mysql_alternative_db: " . $e->getMessage());
                        try {
                            $result = DB::connection('mysql_petlink')->table($table)
                                ->where('id', $user_id)
                                ->pluck($column)
                                ->first();

                            if ($result !== null) {
                                $email->subject = str_replace($placeholder, $result, $email->subject);
                            }
                        } catch (\Exception $e) {
                            Log::error("Error on second approach: " . $e->getMessage());
                            try {
                                $result = DB::table($table)
                                    ->where('id', $user_id)
                                    ->pluck($column)
                                    ->first();

                                $email->subject = str_replace($placeholder, $result, $email->subject);
                            } catch (\Exception $e) {
                                Log::error("Error on alternative connection with only 'id': " . $e->getMessage());
                            }
                        }
                    }
                }
            }
        }
        preg_match_all('/\{(.*?)\}/', $email->body, $matches);
        $body_array = $matches[0];
        $body_array_string = $matches[1];
        foreach ($body_array as $index => $placeholder) {
            $value = explode('-', $body_array_string[$index]);
            if (count($value) == 2) {
                $table = $value[0];
                $column = $value[1];
                // Fetch the data from the database
                try {
                    // Try the first query with the original connection
                    $result = DB::connection('mysql_petlink')->table($table)
                        ->where('user_id', $user_id)
                        ->pluck($column)
                        ->first();

                    if ($result !== null) {
                        $email->body = str_replace($placeholder, $result, $email->body);
                    }
                } catch (\Exception $e) {
                    Log::error("Error connecting to mysql_petlink: " . $e->getMessage());

                    // Optionally, try another connection if the first one fails
                    try {
                        $result = DB::table($table)
                            ->where('user_id', $user_id)
                            ->pluck($column)
                            ->first();

                        if ($result !== null) {
                            $email->body = str_replace($placeholder, $result, $email->body);
                        }
                    } catch (\Exception $e) {
                        Log::error("Error connecting to mysql_alternative_db: " . $e->getMessage());
                        try {
                            $result = DB::connection('mysql_petlink')->table($table)
                                ->where('id', $user_id)
                                ->pluck($column)
                                ->first();

                            if ($result !== null) {
                                $email->body = str_replace($placeholder, $result, $email->body);
                            }
                        } catch (\Exception $e) {
                            Log::error("Error on second approach: " . $e->getMessage());
                            try {
                                $result = DB::table($table)
                                    ->where('id', $user_id)
                                    ->pluck($column)
                                    ->first();

                                $email->body = str_replace($placeholder, $result, $email->body);
                            } catch (\Exception $e) {
                                Log::error("Error on alternative connection with only 'id': " . $e->getMessage());
                            }
                        }
                    }
                }

                // If the first approach fails, try the query with only 'id' condition


                // Optional: Try another connection with only 'id' condition if needed



                // Replace the placeholder with the result

            }
        }

        // Output the modified email body
        // dd($email);
        return $email->toArray();

        // dd($body);
    }
}
