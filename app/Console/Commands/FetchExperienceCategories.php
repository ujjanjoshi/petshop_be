<?php

namespace App\Console\Commands;

use App\Models\ExperienceCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FetchExperienceCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-experience-categories-data';

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
        $url = config('app.peturl') . '/experiences/categories';
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
            $this->info("process page=$page_count # of experiencesCategories=" . $data['total']);

            $page_count++;
            foreach ($data['data'] as $data) {

                $category = ExperienceCategory::firstWhere('category_id', $data['id']);
                if ($category == null) {
                    $category = new ExperienceCategory;
                }
                $category->fill([
                    'category_id' => $data['id'],
                    'image'     => $data['image'],
                    'name'      => $data['name'],
                    'parent_id' => $data['parent_id'],
                ]);
                $category->save();
            }
        }
    }
}
