<?php

namespace App\Console\Commands;

use App\Models\TicketProduction;
use App\Models\TicketProductionPerformer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FetchTicketProduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-ticket-production-data {--keep} {--provider=tevo} {--since=} {--productionId=} {--page=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Ticket Productions & Merge';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $since = $this->option('since');
        if ($since == null) {
            $since = '1970-01-01';
        }

        $url = '/tickets/events';

        $provider = $this->option('provider');
        $page_count = $this->option('page');
        while (true) {
            $queryParams = [
                'supplier' => $provider,
                'since' => $since,
                'page' => $page_count,
                'limit' => 500
            ];

            $productionId = $this->option('productionId');
            if ($productionId) {
                $url .= "/". $productionId;
            }
            $response = Http::petapi()->get($url, $queryParams);

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
            if ($productionId) {
                $data['data'] = [$data['data']];
                $data['total']= 1;
            }
            $this->info("process page=$page_count # of ticketProduction=". ($data['total'] ?? 1));

            $page_count++;
            foreach ($data['data'] as $data) {
                // merge on when, venue, name
                $ticketProduction = TicketProduction::where('occurred_at', $data['occurred_at'])
                                                    ->where('name',      $data['name'])
                                                /*
                                                    ->whereHas('venue', function ($q) use ($data) {
                                                        $q->where('venue_id', $data['venue_id'])
                                                          ->orWhere('alt_venue_id', $data['venue_id']);
                                                    })
                                                 */
                                                    ->first();
                if ($ticketProduction == null) {
                    $ticketProduction = TicketProduction::firstWhere('production_id', $data['id']);
                }
                //
                // don't overwrite tevo production by tix
                //
                if ($ticketProduction) {
                    if (false == is_numeric($data['id']) && is_numeric($ticketProduction->production_id)) {
                        $this->info("Skip Tix to Tevo update");
                        continue;
                    }
                    //$this->info('update '. $data['name'] .'/'. $data['occurred_at'] .' at '. $data['venue_id']);
                }

                if ($ticketProduction == null) {
                    //$this->info('new '. $data['name'] .'/'. $data['occurred_at'] .' at '. $data['venue_id']);
                    $ticketProduction = new TicketProduction;
                }
                $ticketProduction->production_id = $data['id'];

                if (isset($data['status'])) 
                    $status = $data['status'];
                elseif (isset($data['state'])) 
                    $status = $data['state'] == 'shown' ? 'Active' : $data['state'];
                else
                    $status = 'unknown';

                $ticketProduction->name = $data['name'];
                $ticketProduction->category_id = $data['category_id'];
                $ticketProduction->venue_id = $data['venue_id'];
                $ticketProduction->configuration_id = $data['configuration_id'] ?? 0;
                $ticketProduction->status = $status;
                //$ticketProduction->occurred_at = date('Y-m-d H:i:s', strtotime($data['occurred_at']));
                $ticketProduction->occurred_at = $data['occurred_at'];
                $ticketProduction->popularity_score = $data['popularity_score'] ?? 0;
                try {
                    $ticketProduction->save();
                } catch (\Exception $e) {
                    $this->error("Error saving TicketProduction: ". $e->getMessage());
                }

                // Insertion for tickets_productions_performers
                if (count($data["performers"]) > 0) {
                    foreach ($data["performers"] as $performer) {

                        $productionPerformer = TicketProductionPerformer::where('production_id', $data['id'])
                                                                        ->where('performer_id', $performer['id'])
                                                                        ->first();
                        if ($productionPerformer == null) {
                            $productionPerformer = new TicketProductionPerformer();
                            $productionPerformer->production_id = $data['id'];
                            $productionPerformer->performer_id = $performer['id'];
                            $productionPerformer->save();
                        }
                    }
                }
            }
        }
        if (!$this->option('keep')) {
            // remove any expired production data
            $now = now()->subday(1)->format('Y-m-d');

            $this->info("Cleanup outdated production -- $now");
            TicketProduction::whereDate('occurred_at', '<=', $now)->delete();
        }
    }
}
