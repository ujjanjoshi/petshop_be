<?php

namespace App\Console\Commands;

use App\Models\TourAttraction;
use App\Models\TourAttractionDestination;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchTourAttraction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-tour-attraction';

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
        $url = config('app.peturl') . '/tours/attractions';
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
            $this->info("process page=$page_count # of tourAttraction=" . $data['total']);
            $page_count++;

            foreach ($data['data'] as $data) {
                
                $tour_destination = TourAttraction::firstWhere('id', $data['id']);
                if ($tour_destination == null) {
                    $tour_destination = new TourAttraction();
                }
                $attraction_destination= TourAttractionDestination::where('attraction_id', $data['id'])
                                                                    ->where('destination_id', $data['destination_id'])
                                                                    ->first();
                if ($attraction_destination == null) {
                    $attraction_destination= new TourAttractionDestination();
                    $attraction_destination->fill([
                        "attraction_id"=>$data["id"],
                        "destination_id"=>$data["destination_id"],
                    ]);
                    $attraction_destination->save();
                }
                
                $tour_destination->fill([
                    "id"=>$data["id"],
                    "destination_id"=>$data["destination_id"],
                    "destination_name"=>$data["destination_name"],
                    "title"=>$data["title"],
                    "address"=>$data["address"],
                    "city"=>$data["city"],
                    "state"=>$data["state"],
                    "latitude"=>$data["latitude"],
                    "longitude"=>$data["longitude"],
                    "thumbnail_url"=>$data["thumbnail_url"],
                    "thumbnail_hi_url"=>$data["thumbnail_hi_url"],
                    "rating"=>$data["rating"],
                    "published_at"=>$data["published_at"],
                    "created_at"=>$data["created_at"],
                    "updated_at"=>$data["updated_at"],
                ]);
                $tour_destination->save();
            }
        }
    }
}
