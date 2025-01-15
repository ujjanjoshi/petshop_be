<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Branding;
use App\Models\Email;
use App\Models\MenuBar;
use App\Models\Redeemer;
use App\Models\User;
use App\Models\PaymentCharge;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function getBranding()
    {
        $branding = Branding::get()->toArray();
        return $branding;
    }
    public function getSetting()
    {
        $branding = Branding::get()->toArray();
        $details = [
            "stripe_key" => config('app.stripe_key'),
            "stripe_secret" => config('app.stripe_secret'),
            "petapikey" => config('app.petapikey'),
            "petid" => config('app.petid'),
            "peturl" => config('app.peturl'),
            "mail_mailer"=>config('app.mail_mailer'),
            "mail_host"=>config('app.mail_host'),
            "mail_port"=>config('app.mail_port'),
            "mail_username"=>config('app.mail_username'),
            "mail_password"=>config('app.mail_password'),
            "mail_encryption"=>config('app.mail_encryption'),
            "mail_from_address"=>config('app.mail_from_address'),
            "mail_from_name"=>config('app.mail_from_name'),
            "mail_support_address"=>config('app.mail_support_address'),
            "mail_support_name"=>config('app.mail_support_name'),

        ];
        $data = [
            "branding" => $branding,
            "details" => $details
        ];
        // dd($details);
        return $data;
    }
    public function updateBranding(Request $request, $id)
    {
        // Retrieve the branding record
        $branding = Branding::where('id', $id)->first();
        if ($branding) {
            if ($request->input('address') != null) {
                $branding->address = $request->input('address');
            }
            // Update the fields
    
            if ($request->input('header_color') != null) {
                $branding->header_color = $request->input('header_color');
            }


            if ($request->input('footer_color') != null) {
                $branding->footer_color = $request->input('footer_color');
            }
            if ($request->input('phone_number') != null) {

                $branding->phone_number = $request->input('phone_number');
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
     
            if ($request->input('title') != null) {

                $branding->title = $request->input('title');
            }
            if ($request->input('fax') != null) {

                $branding->fax = $request->input('fax');
            }
            if ($request->input('email') != null) {

                $branding->email = $request->input('email');
            }
            if ($request->input('hours') != null) {

                $branding->hours = $request->input('hours');
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

    public function updateConfigData(Request $request)
    {
        // $stripe_key = $request->stripe_key;
        // $stripe_secret = $request->stripe_value;
        $type = $request->type;
        $petapikey = $request->petapikey;
        $petid = $request->petid;
        $peturl = $request->peturl;
        $mail_mailer =  $request->mail_mailer;
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
            // $this->updateConfig('stripe_key', $stripe_key);
            // $this->updateConfig('stripe_secret', $stripe_secret);
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
                    'user'=>[
                        "name"=>$admin['name'],
                        "email"=>$admin['email'],
                        "is_super"=>$admin['is_super']
                    ]
                ], 200);
                // if ($admin->is_super) {

                // } else {
                // }
            } else {
                $data = ["status" => "fail", "message" => "Email or Password Incorrect", "data" => null];
                return $data;
            }
        } else {
            $data = ["status" => "fail", "message" => "Can't login", "data" => null];
            return $data;
            // return (["status" => false, "message" => "no user found", "data" => null]);
        }
    }
    public function logout(Request $request)
    {
        return response()->json([
            'status' => true,
            'message' => 'Admin Logout Successfully',
            'data'=>null
        ], 200);
    }
    public function showTableAndColumn(Request $request)
    {
        $search = $request->query('search');
        $all_data = [];

        // List of models corresponding to tables you're interested in
        $models = [
            'order_histories' => \App\Models\OrderHistory::class, // Make sure to replace with the correct model paths
            'users' => \App\Models\User::class,
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
        return array_values($filtered_data);
    }

    public function updateEmail(Request $request)
    {
        $id = $request->id;
        $subject = $request->subject;
        $email_body = $request->email_body;
        $email = Email::where('id', $id)->first();
        if($subject!=null){
            $email->subject = $subject;
        }
        if($email_body!=null){
            $email->body = $email_body;
        }
        $email->save();
        return "Email Update Sucessfully";
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
    
    // public function deleteEmail(Request $request)
    // {
    //     $id = $request->id;
    //     Email::where('id', $id)->delete();
    //     return "Email Deleted Sucessfully";
    // }

    // public function storeEmail(Request $request)
    // {
    //     $title = $request->title;
    //     $subject = $request->subject;
    //     $email_body = $request->email_body;
    //     $email = new Email();
    //     $email->title = $title;
    //     $email->subject = $subject;
    //     $email->body = $email_body;
    //     $email->save();
    //     return "Email Added Sucessfully";
    // }
    // public function createEmail()
    // {

    //     return view('Admin.emails.createEmail');
    // }

    public function getUsers(Request $request)
    {
        $page = $request->query('page', 1); // Get the 'page' parameter from the query string, default to 1 if not present
        $search = $request->query('search'); // Assuming you also have a search parameter
        $users = User::with(['redeemer' => function($query) use ($search) {
            $query->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->select(['id', 'first_name', 'last_name', 'email', 'phone']);
        }])
        ->paginate(10, ['*'], 'page', $page);
    
        // Extract and filter the Redeemer data from the User results
        $redeemers = $users->items();
        $redeemers = array_filter(array_map(function($user) {
            return $user->redeemer;
        }, $redeemers));
    
        // Create a new LengthAwarePaginator with the filtered Redeemer data
        $paginatedRedeemers = new LengthAwarePaginator(
            $redeemers,
            $users->total(),
            $users->perPage(),
            $users->currentPage(),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $data=[
            "status"=>"sucess",
            "data"=>$paginatedRedeemers->toArray()
        ];
        return $data;
    }
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
            return 'success';
            // retur?n view('Accounts.registerSuccess', compact('response'));
        } else {
            return 'error';
        }
    }

    //get all admin
    public function getAdmin(Request $request)
    {
        $admins = Admin::select(['id', 'name', 'email'])->paginate(10);
        $data=[
            "status"=>"sucess",
            "data"=>$admins->toArray()
        ];
        return $data;
        // dd($admins);
        // return view('Admin.adminList', compact('admins'));
        // retur?n view('Accounts.registerSuccess', compact('response'));
        // } else {
        // Session::put('Signup-message', 'Email Already Exist! Proceed to Login');
        // $countries_controller = new CountriesController();
        // $get_countries = $countries_controller->getCountries();
        // $data = [
        //     "countries" => $get_countries
        // ];
        // return redirect('/register')->with('data', $data);
        // }
    }


    //remove admin
    public function removeAdmin(Request $request)
    {
        Admin::where('id', $request->query('id'))->delete();
        return ("Admin deleted sucessfully" . $request->query('id'));
    }

    public function getPaymentCharges()
    {
        $paymentCharges = PaymentCharge::select(['id','charges','status','type'])->get();
        return response()->json([
            "success"=>true,
            'message' => 'Payment charges fetch successfully.',
            'payment_charge' => $paymentCharges
        ], 200);
    }

    public function getPaymentChargesByType($type)
    {
        $paymentCharges = PaymentCharge::select(['id','charges','status','type'])->where('type',$type)->get();
        return response()->json([
            "success"=>true,
            'message' => 'Payment charges fetch successfully.',
            'payment_charge' => $paymentCharges
        ], 200);
    }

    public function updatePaymentCharges(Request $request)
    {
        $datas= $request->datas;
        $flag=true;
        foreach ($datas as $data) {
            $paymentCharges = PaymentCharge::select(['id','status','type'])->where('type',$data['type'])->first();  
            if($paymentCharges){
                $paymentCharges->status = $data['status'];
                $paymentCharges->charges = $data['charges'];
                $paymentCharges->save();
                
            }else{
               $flag=false;
            }
        }
        $paymentCharges = PaymentCharge::select(['id','status','type'])->get();  
        if($flag){

            return response()->json([
                "success"=>true,
                'message' => 'updated successfully',
                'payment_charge' => $paymentCharges
            ], 200);
        }else{
            return response()->json([
                "success"=>false,
                'message' => 'invalid',
                'payment_charge' => false
            ], 200);
        }

    }
}
