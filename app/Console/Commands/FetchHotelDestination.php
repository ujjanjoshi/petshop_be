<?php

namespace App\Console\Commands;

use App\Models\HotelDestination;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FetchHotelDestination extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-hotel-destination-data';

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
        $url = config('app.peturl'). '/hotels/destinations';
        $apiKey = config('app.petapikey');
        $id = config('app.petid');
        $page_count = 1;
        while (true) {
            $queryParams = [
                'page' => $page_count,
                'limit' => 100
            ];
    
            $response = Http::withHeaders([
                'SECURITYTOKEN' => $apiKey,
                'RESELLERID' => $id,
            ])->get($url, $queryParams);
            
            if ($response->failed()) {
                $this->error("Error requesting... Abort");
                break;
            }

            $data = $response->json();
            if (count($data['data']) == 0) {
                // no more data to process DONE
                break;
            }

            $this->info("process page=$page_count # of hotelDestination=". $data['total']);

            $page_count++;

            foreach ($data['data'] as $item) {
                $hotel = HotelDestination::firstWhere('code', $item['code']);
                if ($hotel == null) {
                $hotel_destination=new HotelDestination();
                $hotel_destination->name= $item['name'];
                $hotel_destination->code= $item['code'];
                $hotel_destination->country_code= $item['country_code'];
                $hotel_destination->save();
                }
            }

        }
    }
}
