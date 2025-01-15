<?php

namespace App\Console\Commands;

use App\Models\Hotel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FetchHotels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-hotels-data';

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
        $url = config('app.peturl') .'/hotels';
        $apiKey = config('app.petapikey');
        $id = config('app.petid');
        $page_count = 1;
        while(true){
            $queryParams = [
                'page' => $page_count,
                'limit' => 50
            ];
    
            $response = Http::withHeaders([
                'SECURITYTOKEN' => $apiKey,
                'RESELLERID' => $id,
            ])->get($url, $queryParams);    
            if ($response->failed()) {
                $this->error("Error requesting... Abort");
                break;
            }

            // Data received successfully
            $data = $response->json();
            if (count($data['data']) == 0) {
                // no more data to process DONE
                break;
            }
            $this->info("process page=$page_count # of hotels=". $data['total']);

            $page_count++;
            // $count=0;
            foreach ($data['data'] as $item) {
                $phones = json_encode($item["phones"]);
                // Check if experience with the same ID exists
                $hotel = Hotel::firstWhere('code', $item['code']);
                if ($hotel == null) {
                    $hotel = new Hotel();
                }
                    // Set the attributes of the new Hotel object
                    $hotel->giata_id = $item["giata_id"];
                    $hotel->code = $item["code"];
                    $hotel->name = $item["name"];
                    $hotel->description = $item["description"];
                    $hotel->address = $item["address"];
                    $hotel->city = $item["city"];
                    $hotel->state = $item["state"];
                    $hotel->country = $item["country"];
                    $hotel->zip = $item["zip"];
                    $hotel->phone = $item["phone"];
                    $hotel->phones = $phones;
                    $hotel->url = $item["url"];
                    $hotel->email = $item["email"];
                    $hotel->image = $item["image"];
                    $hotel->images = json_encode($item["images"]);
                    $hotel->rating = $item["rating"];
                    $hotel->category_code = $item["category_code"];
                    $hotel->latitude = $item["latitude"];
                    $hotel->longitude = $item["longitude"];
                    $hotel->destination_code = $item["destination_code"];
                    $hotel->rooms = json_encode($item["rooms"]);
                    $hotel->facilities = json_encode($item["facilities"]);
                    $hotel->issues = json_encode($item["issues"]);
                    $hotel->created_at = $item["created_at"];
                    $hotel->updated_at = $item["updated_at"];
                    $hotel->prefer = $item["prefer"];
                    $hotel->comment = $item["comment"];

                    // Save the new Hotel object
                    $hotel->save();
                
            }
        }
        
    }
}
