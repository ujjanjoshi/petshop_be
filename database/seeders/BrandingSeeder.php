<?php

namespace Database\Seeders;

use App\Models\Branding;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
//         header_logo
// footer_logo
// address
// phone_number
// title
// fax
// email
// hours
// header_color
// footer_color
        $data = [
            'header_logo' => 'uploads/global/logo2.png',
            'footer_logo' => 'uploads/global/pulse-whitelogo.png',
            'address'=>'9119 Church St. Manassas VA 20110',
            'phone_number' => '866-904-5577',
            'title' => 'JustRewards Fulfilment Center',
            'fax' => '703-251-2348',
            'email' => 'clientcare@justrewardsteam.com',
            'hours' => 'Friday 9AM to 6PM (EST)',
            'header_color' => '#ffffff',
            'footer_color' => '#1b4865'
        ];
        $brandings = Branding::count();
        if ($brandings > 0) {
            Branding::truncate();
        }
        $branding_data = new Branding();
        $branding_data->header_logo = $data['header_logo'];
        $branding_data->footer_logo = $data['footer_logo'];
        $branding_data->address = $data['address'];
        $branding_data->phone_number = $data['phone_number'];
        $branding_data->title = $data['title'];
        $branding_data->fax = $data['fax'];
        $branding_data->email = $data['email'];
        $branding_data->hours = $data['hours'];
        $branding_data->header_color = $data['header_color'];
        $branding_data->footer_color = $data['footer_color'];
        $branding_data->save();
    }
}
