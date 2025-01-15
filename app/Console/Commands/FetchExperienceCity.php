<?php

namespace App\Console\Commands;

use App\Models\ExperienceCity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FetchExperienceCity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-experience-city-data';

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
        $url = config('app.peturl') . '/experiences/cities';
        $apiKey = config('app.petapikey');
        $id = config('app.petid');
        $page_count = 1;
        while (true) {
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
            $data = $response->json();
            if (count($data['data']) == 0) {
                // no more data to process DONE
                break;
            }
            $this->info("process page=$page_count # of experiencesCity=" . $data['total']);
            $page_count++;

            foreach ($data['data'] as $data) {

                $experience_city = ExperienceCity::firstWhere('id', $data['id']);
                if ($experience_city == null) {
                    $experience_city = new ExperienceCity;
                }
                $experience_city->fill([
                    "id" => $data['id'],
                    "name" => $data['name'],
                    "state" => $data['state'],
                    "state_id" => $data['stateId'],
                    "country" => $data['country'],
                    "country_id" => $data['countryId'],
                ]);
                $experience_city->save();
            }
        }
    }
}
