<?php

namespace App\Console\Commands;

use App\Models\TicketVenue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FetchTicketVenues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-ticket-venues-data {--provider=tevo} {--since=} {--page=1}';

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

        $url = '/tickets/venues';

        $provider = $this->option('provider');
        $page_count=$this->option('page');
        while (true) {
            $queryParams = [
                'supplier' => $provider,
                'since' => $since,
                'page' => $page_count,
                'limit' => 500
            ];
    
            $response = Http::petapi()->get($url, $queryParams);
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
            $this->info("process page=$page_count # of ticketVenues=". $data['total']);

            $page_count++;

            foreach ($data['data'] as $data) {
                $ticketVenue = TicketVenue::firstWhere('name', $data['name']);
                if ($ticketVenue == null) {
                    $ticketVenue = new TicketVenue();
                    if ($provider == 'tix')
                        $ticketVenue->alt_venue_id = $data['id'];
                }

                $ticketVenue->venue_id = $data['id'];
                $ticketVenue->name = $data['name'];
                $ticketVenue->events_count = $data['events_count'];
                $ticketVenue->address = $data['address'];
                $ticketVenue->address2 = $data['address2'];
                $ticketVenue->city = $data['city'];
                $ticketVenue->state = $data['state'];
                $ticketVenue->country = $data['country'] ?? $data['country_code'] ?? '';
                $ticketVenue->postal_code = $data['postal_code'];
                $ticketVenue->latitude = $data['latitude'];
                $ticketVenue->longitude = $data['longitude'];
                $ticketVenue->country_code = $data['country_code'];
                $ticketVenue->popularity_score = $data['popularity_score'] ?? 0;

                //$ticketVenue->created_at = $data['created_at'];
                //$ticketVenue->updated_at = $data['updated_at'];
                
                try {
                    $ticketVenue->save();
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
        }
     
    }
}
