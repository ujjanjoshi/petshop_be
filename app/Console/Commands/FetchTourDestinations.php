<?php

namespace App\Console\Commands;

use App\Models\TourDestination;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchTourDestinations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-tour-destinations';

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
        $url = config('app.peturl') . '/tours/destinations';
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
            $this->info("process page=$page_count # of tourDestination=" . $data['total']);
            $page_count++;

            foreach ($data['data'] as $data) {

                $tour_destination = TourDestination::firstWhere('id', $data['id']);
                if ($tour_destination == null) {
                    $tour_destination = new TourDestination;
                }
                $tour_destination->fill([
                    "id"=>$data["id"],
                    "parent_id"=>$data["parent_id"],
                    "lookup_id"=>$data["lookup_id"],
                    "type"=>$data["type"],
                    "name"=>$data["name"],
                    "latitude"=>$data["latitude"],
                    "longitude"=>$data["longitude"],
                    "timezone"=>$data["timezone"],
                    "iata_code"=>$data["iata_code"],
                    "currency_code"=>$data["currency_code"],
                    'created_at'=>$data['created_at'],
                    'updated_at'=>$data['updated_at'],
                ]);
                $tour_destination->save();
            }
        }
    }
}
