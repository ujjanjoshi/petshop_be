<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Experience;
use App\Models\ExperienceCategory;
use App\Models\ExperienceLocation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FetchExperiences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-experiences-data {--page=1} {--sku=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Experiences';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = '/experiences';

        $page_count = $this->option('page');
        while (true) {

            $queryParams = [
                'page' => $page_count,
                'limit' => 500
            ];

            $sku = $this->option('sku');
            if ($sku) {
                $url .= "/". $sku;
            }

            $response = Http::petapi()->get($url, $queryParams);
            if ($response->failed()) {
                $this->error("Error requesting... Abort");
                break;
            }

            // Data received successfully
            $json = $response->json();
            if (count($json['data']) == 0) {
                // no more data to process DONE
                break;
            }
            $this->info("process page=$page_count # of experiences=". $json['total']);

            $page_count++;

            foreach ($json['data'] as $data) {
                $experience = Experience::firstWhere('experience_id', $data['id']);
                if ($experience == null) {
                    $experience = new Experience;
                    $experience->experience_id = $data['id'];
                }
                $experience->fill([
                    'sku' => $data['sku'],
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'short_desc' => $data['short_desc'],
                    'image' => $data['image'],
                    'thumbnail' => $data['thumbnail'],
                    'retail_price' => $data['retail_price'],
                    'wholesale_price' => $data['wholesale_price'],
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['updated_at'],
                ]);
                $experience->save();

                // reset the category/location
                $experience->categories()->detach();
                $experience->locations()->detach();

                // Insert categories
                foreach ($data['category'] as $categoryData) {

                    $category = ExperienceCategory::where('category_id', $categoryData['id'])->first();
                    if ($category == null) {
                        $category = new ExperienceCategory;
                    }
                    $category->fill([
                        'category_id' => $categoryData['id'],
                        'name' => $categoryData['name'],
                        'image' => $categoryData['image'],
                        'parent_id' => $categoryData['parent_id'],
                    ]);
                    $category->save();

                    // Attach category to experience
                    $experience->categories()->attach($category);
                }

                // Insert locations
                foreach ($data['location'] as $locationData) {
                    $location = ExperienceLocation::where('location_id', $locationData['id'])->first();
                    if ($location == null) {
                        $location = new ExperienceLocation;
                    }
                    $location->fill([
                        'location_id' => $locationData['id'],
                        'name' => $locationData['name'],
                        'city' => $locationData['city'],
                        'state' => $locationData['state'],
                        'state_id' => $locationData['state_id'],
                        'country' => $locationData['country'],
                        'country_id' => $locationData['country_id'],
                        'experience_id'=>$data['id']
                    ]);
                    $location->save();

                    // Attach location to experience
                    $experience->locations()->attach($location);
                }
            }
        } // do-while
    }
}
