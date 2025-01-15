<?php

namespace App\Console\Commands;

use App\Models\TicketPerformer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FetchTicketPerformer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-ticket-performer-data {--provider=tevo} {--since=} {--page=1}';

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

        $url = '/tickets/performers';

        $provider = $this->option('provider');
        $page_count = $this->option('page');
        while (true) {
            $queryParams = [
                'supplier' => $provider,
                'since' => $since,
                'page' => $page_count,
                'limit' => 500
            ];

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
            $this->info("process page=$page_count # of ticketPerformer=" . $data['total']);

            $page_count++;

            foreach ($data['data'] as $data) {

                $createdAt = Carbon::parse($data['created_at']);
                $updatedAt = Carbon::parse($data['updated_at']);

                // Now format the Carbon objects into the MySQL datetime format
                $createdAtFormatted = $createdAt->toDateTimeString();
                $updatedAtFormatted = $updatedAt->toDateTimeString();
                $ticketPerformer = TicketPerformer::firstWhere('name', $data['name']);
                if ($ticketPerformer == null) {
                    $ticketPerformer = new TicketPerformer();
                    if ($provider == 'tix')
                        $ticketPerformer->alt_performer_id = $data['id'];
                }

                $ticketPerformer->performer_id = $data['id'];
                $ticketPerformer->name = $data['name'];
                $ticketPerformer->events_count = $data['events_count'];
                $ticketPerformer->popularity_score = $data['popularity_score'] ?? 0;

                //$ticketPerformer->created_at = $createdAtFormatted;
                //$ticketPerformer->updated_at = $updatedAtFormatted;
                
                try {
                    $ticketPerformer->save();
                } catch (\Exception $e) {
                    $this->error($e->getMessage);
                }
            }
        }
    }
}
