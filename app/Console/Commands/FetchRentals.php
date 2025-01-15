<?php

namespace App\Console\Commands;

use App\Models\AvailabilityRental;
use App\Models\ImageRental;
use App\Models\RateRental;
use App\Models\UnitRental;
use App\Models\VacationRental;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchRentals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-rentals {--since=} {--page=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $since = $this->option('since');
        if ($since == null) {
            $since = '1970-01-01';
        }

        $page_count = $this->option('page');
        while (true) {

            $queryParams = [
                'since' => $since,
                'page' => $page_count,
                'limit' => 500
            ];
            $response = Http::petapi()->get('/rentals', $queryParams);

            // Check if the request was successful
            if ($response->failed()) {
                $this->error("Error requesting... Abort");
                break;
            }

            // Data received successfully
            $json = $response->json();
            if (empty($json['data']) || count($json['data']) == 0) {
                // no more data to process DONE
                if ($page_count == 1)
                    $this->info("No data available: lastUpdated - $since");
                break;
            }
            $this->info("process page=$page_count # of rentals=" . $json['total']);

            $page_count++;

            foreach ($json['data'] as $data) {
                $rental = VacationRental::firstWhere('id', $data['id']);
                if ($rental == null) {
                    $rental = new VacationRental();
                    $rental->id = $data['id'];
                }
                $rental->fill([
                    'active' => $data['active'],
                    'name' => $data['name'],
                    'headline' => $data['headline'],
                    'summary' => $data['summary'],
                    'description' => $data['description'],
                    'story' => $data['story'],
                    'benefits' => $data['benefits'],
                    'features' => $data['features'],
                    'address1' => $data['address1'],
                    'address2' => $data['address2'],
                    'city' => $data['city'],
                    'state' => $data['state'],
                    'country' => $data['country'],
                    'zip' => $data['zip'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'show_exact_location' => $data['show_exact_location'],
                    'nearest_places' => $data['nearest_places'],
                    'contact_email' => $data['contact_email'],
                    'contact_fax' => $data['contact_fax'],
                    'language_spoken' => $data['language_spoken'],
                    'contact_name' => $data['contact_name'],
                    'contact_phone' => $data['contact_phone'],
                    'contact_phone2' => $data['contact_phone2'],
                    'contact_phone3' => $data['contact_phone3'],
                    'updated_at' => $data['updated_at'],

                ]);
                $rental->save();

                $image_rental = ImageRental::firstWhere('rental_id', $data['id']);
                if ($image_rental == null) {
                    $image_rental = new ImageRental();
                    $image_rental->rental_id = $data['id'];
                }
                $image_rental->image = json_encode($data['images']);
                $image_rental->save();
               

                $unit_rental = UnitRental::firstWhere('rental_id', $data['id']);
                if ($unit_rental == null) {
                    $unit_rental = new UnitRental();
                    $unit_rental->rental_id = $data['id'];
                }
                $unit_rental->unit = json_encode($data['units']);
                $unit_rental->save();

                if (isset($data['availability'])) {
                    $availability_rental = AvailabilityRental::firstWhere('rental_id', $data['id']);
                    if ($availability_rental == null) {
                        $availability_rental = new AvailabilityRental();
                        $availability_rental->rental_id = $data['availability']["property_id"];
                    }
                    $availability_rental->availability_id = $data['availability']["id"];
                    $availability_rental->begin_date = $data['availability']["begin_date"];
                    $availability_rental->end_date = $data['availability']["end_date"];
                    $availability_rental->availability_total = $data['availability']["availability"];
                    $availability_rental->change_over = $data['availability']["change_over"];
                    $availability_rental->min_prior_notify = $data['availability']["min_prior_notify"];
                    $availability_rental->max_stay = $data['availability']["max_stay"];
                    $availability_rental->min_stay = $data['availability']["min_stay"];
                    $availability_rental->stay_increment = $data['availability']["stay_increment"];
                    $availability_rental->minStay = $data['availability']['configuration']["minStay"];
                    $availability_rental->changeOver = $data['availability']['configuration']["changeOver"];
                    $availability_rental->availability = $data['availability']['configuration']["availability"];
                    $availability_rental->minPriorNotify = $data['availability']['configuration']["minPriorNotify"];
                    $availability_rental->created_at = $data['availability']['created_at'];
                    $availability_rental->updated_at = $data['availability']['updated_at'];
                    $availability_rental->save();
                }

                foreach ($data['rates'] as $rate) {
                    $rate_rental = RateRental::Where('rental_id', $data['id'])->where('rate_id',$rate['id'])->first();
                    if ($rate_rental == null) {
                        $rate_rental = new RateRental();
                        $rate_rental->rate_id = $rate["id"];
                    }
                    $rate_rental->rental_id = $rate["property_id"];
                    $rate_rental->begin_date = $rate["begin_date"];
                    $rate_rental->end_date = $rate["end_date"];
                    $rate_rental->name = $rate["name"];
                    $rate_rental->min_stay = $rate["min_stay"];
                    $rate_rental->note = $rate["note"];
                    $rate_rental->amount = $rate["amount"];
                    $rate_rental->type = $rate["type"];
                    $rate_rental->save();
                }
            }
        } // do-while
    }
}
