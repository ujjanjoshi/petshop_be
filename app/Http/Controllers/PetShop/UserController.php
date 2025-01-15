<?php

namespace App\Http\Controllers\PetShop;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CountryController;
use App\Jobs\SendForgetPasswordMailJobs;
use App\Jobs\SendVerificationMailJobs;
use App\Models\Country;
use App\Models\Currency;
use App\Models\PetShop\Email;
use App\Models\PetShop\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

// session_start();
class UserController extends Controller
{
    public function signup(Request $request)
    {
        /*
         * custom message for password regex rule
         */
        $messages = [
            'password.regex' => 'Password must contain at least one digit, one special character and both upper & lowercase letters.',
            'email.required' => 'Email must be unique',
        ];

        Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:254'],
            'last_name'  => ['required', 'string', 'max:254'],
            'address1' => ['required', 'string', 'max:90'],
            'address2' => ['nullable', 'string', 'max:90'],
            'city' => ['required', 'string', 'max:32'],
            'state' => ['required', 'string', 'max:32'],
            'postal_code' => ['required', 'string', 'max:16'],
            'phone' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:254', 'unique:mysql_pet_shop.users'],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',        // must be at least 1 lowercase
                'regex:/[A-Z]/',        // must be at least 1 uppercase
                'regex:/[0-9]/',        // must be at least 1 uppercase
                'regex:/[!@#$%^&*]/',   // must be at least 1 special character
            ],
            //'confirmed'],
        ], $messages)->validate();

        // GENERATE RANDOM CODE FOR EMAIL
        $mail_code = rand(100000, 999999);

        // UUID - Laravel uses UUID as primary key, you may use incrementing integer id if preferred
        $user_id = uniqid();

        // GENERATE PASSWORD AND DATETIME
        $password = password_hash($request->password, PASSWORD_DEFAULT);
        $phone = str_replace(' ', '', $request->phone);
        $phone = str_replace('-', '', $phone);

        // GET DEFAULT CURRENCY NAME - This part might need to be adjusted based on your Laravel app structure
        $currency_id = Currency::where('default', 1)->value('name');

        $user = new User();
        $user->user_id = $user_id;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->phone_country_code = $request->phone_country_code;
        $user->email = $request->email;
        $user->phone = $phone;
        $user->address1 = $request->address1;
        if ($user->address2 != null) {
            $user->address2 = $request->address2;
        }
        $user->city = $request->city;
        $user->state = $request->state;
        $user->country_code = $request->phone_country_code;
        $user->postal_code = $request->postal_code;
        $user->email_code = $mail_code;
        $user->currency_id = $currency_id;
        $user->password = $password;
        $user->status = $request->status ?? 0;
        $user->user_type = "Customer";
        $user->created_at = now();
        $user->email_send_datetime = new DateTime();
        $user->save();
        
        $this->sendVerificationEmail($request->base_url,$request->email);

        // RETURN RESPONSE
        $response = [
            "status" => true,
            "message" => "Account registered successfully.",
            "data" => $user
        ];
        return response()->json($response);
    }

    public function activateAccount($email)
    {
        $decryptedEmail = decrypt($email);
        $email_send_date_time = User::where('email', $decryptedEmail)->select('email_send_datetime', 'updated_at')->first();
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $email_send_date_time->email_send_datetime);

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
        if ($hoursDifference < 2 /* && $email_send_date_time->email_send_datetime > $email_send_date_time->updated_at */) {
            $user_info = User::where('email', $decryptedEmail)->update(['status' => 1]);
            $response = [
                "status" => true,
                "message" => "Activated Sucessfully",
            ];
            return response()->json($response);
        } else {
            Log::error("expired email activation ==>" . $decryptedEmail);

            $data = ["status" => "error", "message" => "Link Expired!! Please login for new link", "data" => null];
            return response()->json($data);
        }
    }


    public function login(Request $request)
    {
        Session::forget('error');
        // // VALIDATION
        // $validatedData = request()->validate([
        //     'email' => 'required|email',
        //     'password' => 'required',
        // ]);

        // SELECT USER FROM DATABASE
        // dd(md5($request->password));
        $user = User::firstWhere('email', $request->email);
        if ($user && password_verify($request->password, $user->password)) {

            if ($user->status == 0) {
                $data = ["status" => false, "message" => "Account not verified yet", "data" => $user];

                $data['email'] = $request->email;
                
                $this->sendVerificationEmail($request->base_url,$request->email);

                $user->email_send_datetime = new DateTime();
                $user->save();

                Session::put('error', $data);
                $response = [
                    "status" => false,
                    "message" => "Your Account not verified yet. Please check your email for the verification email.",
                    "data" => $user
                ];
                return response()->json($response);

                // return view('Accounts.login',compact('$data'));
                // return (["status" => false, "message" => "user account not verified", "data" => $user]);
            }

            // HOOK
            // event(new UserLoggedIn($user)); // You need to define UserLoggedIn event

            // INSERT TO LOGS
            // $user_id = $user->uer_id;
            // $log_type = "login";
            // $datetime = now();
            // $desc = "user logged into account" . Request::ip();
            // logs($user_id, $log_type, $datetime, $desc); // Assuming logs() function is defined elsewhere
            //
            //
            $tokenResult = $user->createToken('pet-shop-token')->plainTextToken;
            $accessToken = $tokenResult;

            Session::put('login_token', $accessToken);
            $country = Country::where('phonecode', $user->phone_country_code)->first();
            $user->country_code = $country->name;
            $data = ["status" => "success", "message" => "Login Successfully",  "token" => $accessToken, "data" => $user];
            return response()->json($data);
        }

        //$data = ["status" => "error", "message" => "User Not Found", "data" => null];
        $data = ["status" => "error", "message" => "Email or Password Incorrect", "data" => null];
        Session::put('error', $data);
        return response()->json($data);
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
        $user_id = $request->user()->user_id;
        $user = User::where('user_id', $user_id)->first();
        if ($user) {
            if ($request->first_name !== null) {
                $user->first_name = $request->first_name;
            }

            if ($request->last_name !== null) {
                $user->last_name = $request->last_name;
            }

            if ($request->phone_country_code !== null) {
                $user->phone_country_code = $request->phone_country_code;
            }

            if ($request->phone !== null) {
                $user->phone = str_replace(' ', '', $request->phone);
            }

            if ($request->address1 !== null) {
                $user->address1 = $request->address1;
            }

            if ($request->address2 !== null) {
                $user->address2 = $request->address2;
            }

            if ($request->city !== null) {
                $user->city = $request->city;
            }

            if ($request->state !== null) {
                $user->state = $request->state;
            }

            if ($request->phone_country_code !== null) {
                $user->country_code = $request->phone_country_code;
            }

            if ($request->postal_code !== null) {
                $user->postal_code = $request->postal_code;
            }

            $user->save();

            $user_info = User::where('id', $user->id)->first();
            $country = Country::where('phonecode', $user_info->phone_country_code)->first();
            if($country!=null){
                $user_info->country_code = $country->name;
            }
            $countries_controller = new CountryController();
            $get_countries = $countries_controller->getCountries();
            return response()->json([
                "status" => "success",
                "message" => "User updated successfully",
                "data" => [
                    'user' => $user_info,
                    'countries' => $get_countries
                ]
            ]);
        }

        $data = ["status" => "error", "message" => "Expired!!", "data" => null];
        response()->json($data);
    }

    public function changePassword(Request $request)
    {
        $user_id = $request->user()->user_id;
        $user = User::where('user_id', $user_id)->first();
        if ($user) {

            $old_password = $request->old_password;
            if (!password_verify($old_password, $user->password)) {
                return back()->withInput()->withErrrors(['old_password' => 'invalid old password']);
            }

            $messages = [
                'new_password.regex' => 'Password must contain at least one digit, one special character and both upper & lowercase letters.',
            ];

            /*
                 * Not Authenticated ???
                $user2 = \Auth::user();
                \Log::info('auth:user='. json_encode($user2));
                 */

            $new_password = $request->new_password;
            $validator = Validator::make($request->all(), [
                //   'old_password' => ['required', 'string', 'current_password:web'],
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

            $password = password_hash($new_password, PASSWORD_DEFAULT);
            $user->password = $password;
            $user->save();
            $data = [
                "status" => "success",
                "message" => "Password change successfully"

            ];
            return response()->json($data);
        }

        $data = ["status" => "error", "message" => "Expired!!", "data" => null];
        return response()->json($data);
    }

    public function sendResetPasswordLink(Request $request)
    {
        $email = $request->input('email');

        $user = User::where('email', $email)->where('status', 1)->first();
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

            $email_message = ["status" => true, "message" => "Reset Password Email Sent", "data" => $user];
            return response()->json($email_message)  ;
        }
     
    }
    public function resetPasswordForm($email)
    {
        $decryptedEmail = decrypt($email);
        $email_send_date_time = User::where('email', $decryptedEmail)->select('email_send_datetime', 'updated_at')->first();
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $email_send_date_time->email_send_datetime);
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
        if ($hoursDifference < 2 /* && $email_send_date_time->email_send_datetime > $email_send_date_time->updated_at */) {
           return response()->json($email);
        } else {
            $data = ["status" => "error", "message" => "Link Expired!! Please request for new link", "data" => null];
            return response()->json($data);
        }
    }

    public function changeResetPassword(Request $request)
    {
        $new_password = $request->new_password;

        $confirm_password = $request->confirm_password;
        // dd($confirm_password);
        $email = $request->email;
        $decryptedEmail = decrypt($request->email);
        $user = User::where('email', $decryptedEmail)->first();
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
            $data=[
                'status'=>true,
                "message"=>'Password reset successfully'
            ];
            return response()->json($data);
        }
      
    }

    public function getUserData(Request $request)
    {
            $countries_controller = new CountryController();
            $get_countries = $countries_controller->getCountries();
            $data = [
                "status"=>true,
                "data"=>[
                    "countries" => $get_countries,
                    "user"=>$request->user()
                ]
              
            ];
           return $data;
    }

    public function emailTemplate($id, $user_email)
    {
        $user_id = User::where('email', $user_email)->pluck('user_id')
            ->first();
        $email = Email::where("id", $id)->select(['subject', 'body'])->first();
        preg_match_all('/\{(.*?)\}/', $email->subject, $matches);
        $subject_array = $matches[0];
        $subject_array_string = $matches[1];
        foreach ($subject_array as $index => $placeholder) {
            $value = explode('-', $subject_array_string[$index]);
            if (count($value) == 2) {
                $table = $value[0];
                $column = $value[1];

                // Fetch the data from the database
                $result = DB::table($table)
                    ->where('user_id', $user_id)
                    ->orderBy('created_at', 'desc') // Adjust this condition as needed
                    ->pluck($column)
                    ->first();

                // Replace the placeholder with the result
                $email->subject = str_replace($placeholder, $result, $email->subject);
            }
        }
        preg_match_all('/\{(.*?)\}/', $email->body, $matches);
        $body_array = $matches[0];
        $body_array_string = $matches[1];
        foreach ($body_array as $index => $placeholder) {
            $value = explode('-', $body_array_string[$index]);
            // dd($value);
            if (count($value) == 2) {
                $table = $value[0];
                $column = $value[1];
                // dd($table);
                // Fetch the data from the database
                $result = DB::table($table)
                    ->where('user_id', $user_id)
                    ->orderBy('created_at', 'desc') // Adjust this condition as needed
                    ->pluck($column)
                    ->first();

                // Replace the placeholder with the result
                $email->body = str_replace($placeholder, $result, $email->body);
            }
        }

        // Output the modified email body
        // dd($email);
        return $email->toArray();
    }

    public function sendVerificationEmail($base_url,$email)
    {
        $email_template = $this->emailTemplate(1, $email);
        $data = [
            'email' => $email,
            'subject' => $email_template['subject'],
            'body' => $email_template['body'],
            'base_url'=>$base_url
        ];
        dispatch(new SendVerificationMailJobs($data));
    }
}
