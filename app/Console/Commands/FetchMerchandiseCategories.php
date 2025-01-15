<?php

namespace App\Console\Commands;

use App\Models\MerchandiseCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchMerchandiseCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-merchandise-categories';

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
        $url = config('app.peturl'). '/merchandises/categories';
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

            $this->info("process page=$page_count # of merchandiseCategory=". $data['total']);

            $page_count++;
            foreach ($data['data'] as $item) {
                $merchandise = MerchandiseCategory::firstWhere('id', $item['id']);
                if ($merchandise == null) {
                    $merchandise_category=new MerchandiseCategory();
                    $merchandise_category->id= $item['id'];
                    $merchandise_category->name= $item['name'];
                    $merchandise_category->parent_id= $item['parent_id'];
                    $merchandise_category->product_count= $item['products_count'];
                    $merchandise_category->save();
                }
            }

        }
    }
}
