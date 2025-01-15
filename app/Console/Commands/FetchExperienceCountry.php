<?php

namespace App\Console\Commands;

use App\Models\ExperienceCountry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FetchExperienceCountry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-experience-country-data';

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
        $url = config('app.peturl'). '/experiences/countries';
        $apiKey = config('app.petapikey');
        $id = config('app.petid');
        $page_count=1;
        while(true){
            $queryParams = [
                'page' => $page_count,
                'limit' => 500
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
            $this->info("process page=$page_count # of experiencesCountry=". $data['total']);

            $page_count++;
            foreach ($data['data'] as $data) {
                $country = ExperienceCountry::firstWhere("id", $data['id']);
                if ($country == null) {
                    $country=new ExperienceCountry;
                    // ExperienceCountry::create();
                }
                $country->fill([
                    "id" => $data['id'],
                    "name" => $data['name'],
                    "code" => $data['code'],
                    "region" => $data['region']
                ]);
                $country->save();
            }
        }
       
    }
}
