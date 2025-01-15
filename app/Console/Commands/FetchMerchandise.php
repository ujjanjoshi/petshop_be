<?php

namespace App\Console\Commands;

use App\Models\Merchandise;
use App\Models\MerchandiseDimension;
use App\Models\MerchandiseFeature;
use App\Models\MerchandiseOption;
use App\Models\MerchandiseResource;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchMerchandise extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-merchandise {--keep} {--page=1}';

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
        $page_count = $this->option('page');
        while (true) {
            $queryParams = [
                'page' => $page_count,
                'limit' => 500
            ];

            $response = Http::petapi()->get('/merchandises', $queryParams);
            if ($response->failed()) {
                $this->error("Error requesting... Abort");
                break;
            }

            $data = $response->json();
            if (count($data['data']) == 0) {
                // no more data to process DONE
                break;
            }

            $this->info("process page=$page_count # of merchandise=" . $data['total']);

            $page_count++;
            foreach ($data['data'] as $item) {
                $dimension_id = 0;

                if ($item['dimension']) {
                    $merchandise_dimension = new MerchandiseDimension();
                    $merchandise_dimension->width = $item['dimension']["width"];
                    $merchandise_dimension->height = $item['dimension']["height"];
                    $merchandise_dimension->length = $item['dimension']["length"];
                    $merchandise_dimension->save();
                    $dimension_id = $merchandise_dimension->id;
                }

                $merchandise = Merchandise::firstWhere('product_id', $item['id']);
                if ($merchandise == null) {
                    $merchandise = new Merchandise();
                    $merchandise->product_id = $item['id'];
                }
                $merchandise->name = $item['name'];
                $merchandise->description = $item['description'];
                $merchandise->brand = $item['brand'];
                $merchandise->model = $item['model'];
                $merchandise->upc = $item['upc'];
                $merchandise->weight = $item['weight'];
                $merchandise->dimension_id = $dimension_id;
                $merchandise->image_lo = $item['image_lo'];
                $merchandise->image_hi = $item['image_hi'];
                $merchandise->selling_price = floatval(str_replace(',', '', $item['selling_price']));
                $merchandise->ship_in_days = $item['ship_in_days'];
                $merchandise->prop65 = $item['prop65'];
                $merchandise->prop65_message = $item['prop65_message'];
                $merchandise->category_id = $item['category']['id'];
                $merchandise->updated_at_date = $item['updated_at'];
                $merchandise->save();

                $merchandise_id= $merchandise->id;

                $merchandise->features()->delete();
                if (count($item['features']) > 0) {
                    foreach ($item['features'] as $feature) {
                        $merchandise_feature = new MerchandiseFeature();
                        $merchandise_feature->feature = $feature['feature'];
                        $merchandise_feature->featureSort = $feature['featureSort'];
                        $merchandise_feature->merchandise_id = $merchandise_id;
                        $merchandise_feature->save();
                    }
                }

                $merchandise->resources()->delete();
                if (count($item['resources']) > 0) {
                    foreach ($item['resources'] as $resource) {
                        $merchandise_resource = new MerchandiseResource();
                        $merchandise_resource->brandResourceLink = $resource['brandResourceLink'];
                        $merchandise_resource->brandResourceName = $resource['brandResourceName'];
                        $merchandise_resource->merchandise_id = $merchandise_id;
                        $merchandise_resource->save();
                    }
                }

                $merchandise->options()->delete();
                if (count($item['options']) > 0) {
                    foreach ($item['options'] as $option) {
                        $merchandise_resource = new MerchandiseOption();
                        $merchandise_resource->product_id = $option['product_id'];
                        $merchandise_resource->name = $option['name'];
                        $merchandise_resource->model = $option['model'];
                        $merchandise_resource->upc = $option['upc'];
                        $merchandise_resource->status = $option['status'];
                        $merchandise_resource->size = $option['size'];
                        $merchandise_resource->color = $option['color'];
                        $merchandise_resource->label1 = $option["label1"];
                        $merchandise_resource->value1 = $option["value1"];
                        $merchandise_resource->label2 = $option["label2"];
                        $merchandise_resource->value2 = $option["value2"];
                        $merchandise_resource->label3 = $option["label3"];
                        $merchandise_resource->value3 = $option["value3"];
                        $merchandise_resource->image_lo = $option['image_lo'];
                        $merchandise_resource->image_hi = $option['image_hi'];
                        $merchandise_resource->upcharge_cost = $option['upcharge_cost'];
                        $merchandise_resource->resources = json_encode($option['resources']);
                        $merchandise_resource->merchandise_id = $merchandise_id;

                        $merchandise_resource->save();
                    }
                }
             
            }
        }
        if (!$this->option('keep')) {
            // remove any expired production data
            $this->info("Cleanup outdated merchandise");

            // clear the dimension object... 
            MerchandiseDimension::whereDate('created_at', '!=', now()->format('Y-m-d'))->delete();

            $now = now()->subday(1)->format('Y-m-d');
            foreach (Merchandise::whereDate('updated_at', '<=', $now)->get() as $merchandise) {

                try {
                    $merchandise->features()->delete();
                    $merchandise->resources()->delete();
                    $merchandise->options()->delete();
                    $merchandise->delete();
                } catch (\Exception $e) {
                    $this->error("Error deleting: ". $e->getMessage());
                }
            }
        }
    }
}
