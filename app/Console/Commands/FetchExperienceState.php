<?php

namespace App\Console\Commands;

use App\Models\ExperienceState;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FetchExperienceState extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-experience-state-data';

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
        $url = config('app.peturl') .'/experiences/states';
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

            // // Check if the request was successful
            if ($response->failed()) {
                $this->error("Error requesting... Abort");
                break;
            }

            // Data received successfully
            $data = $response->json();
            print_r($data);
            if (count($data['data']) == 0) {
                // no more data to process DONE
                break;
            }
            $this->info("process page=$page_count # of experiencesState=". $data['total']);

            $page_count++;
            foreach ($data['data'] as $data) {

                $experience_state = ExperienceState::firstWhere('id', $data['id']);
                if ($experience_state == null) {
                    $experience_state=new ExperienceState;
                }
                $experience_state->fill([
                    "id" => $data['id'],
                    "name" => $data['name'],
                    "code" => $data['code'],
                    "country_id" => $data['countryId'],
                    "country_name" => $data['country'],
                ]);
                $experience_state->save();
            }
        }
    }
}
