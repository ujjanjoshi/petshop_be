<?php

namespace App\Console\Commands;

use App\Models\TicketCategory;
use App\Models\TicketChildren;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FetchTicketCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-ticket-categories-data {--provider=tevo} {--since=}';

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

        $since = $this->option('since');
        if ($since == null) {
            $since = '1970-01-01';
        }

        $url = config('app.peturl') . '/tickets/categories';
        $apiKey = config('app.petapikey');
        $id = config('app.petid');
        $page_count = 1;

        $provider = $this->option('provider');
        while (true) {
            $queryParams = [
                'supplier'  => $provider,
                'since'  => $since,
                'page' => $page_count,
                'limit' => 500
            ];

            $response = Http::withHeaders([
                'SECURITYTOKEN' => $apiKey,
                'RESELLERID' => $id,
            ])->get($url, $queryParams);

            // Check if the request was successful
            if ($response->failed()) {
                $this->error("Error requesting... Abort");
                break;
            }

            // Data received successfully
            $data = $response->json();
            if (count($data['data']) == 0) {
                // no more data to process DONE
                if ($page_count == 1)
                    $this->info("No data available: lastUpdated - $since");
                break;
            }
            $this->info("process page=$page_count # of ticketCategories=" . $data['total']);

            $page_count++;

            foreach ($data['data'] as $data) {
                $ticketCategory = TicketCategory::firstWhere('name', $data['name']);

                $createdAt = Carbon::parse($data['created_at']);
                $updatedAt = Carbon::parse($data['updated_at']);

                // Now format the Carbon objects into the MySQL datetime format
                $createdAtFormatted = $createdAt->toDateTimeString();
                $updatedAtFormatted = $updatedAt->toDateTimeString();
                if ($ticketCategory == null) {
                    $ticketCategory = new TicketCategory();
                    if ($provider == 'tix')
                        $ticketCategory->alt_category_id = $data['id'];
                }
                $ticketCategory->category_id = $data['id'];
                $ticketCategory->name = $data['name'];
                $ticketCategory->parent_id = (string) $data['parent_id'];
                $ticketCategory->featured = $data['featured'] ?? 0;
                $ticketCategory->events_count = $data['events_count'];

//                $ticketCategory->updated_at = $updatedAtFormatted;
//                $ticketCategory->updated_at = $updatedAtFormatted;
                
                $ticketCategory->save();
            }
        }
    }
}
