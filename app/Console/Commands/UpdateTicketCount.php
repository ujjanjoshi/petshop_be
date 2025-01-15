<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TicketPerformer;
use App\Models\TicketCategory;
use App\Models\TicketVenue;

class UpdateTicketCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-ticket-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Ticket/Production count for Performer, Category, Venue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('max_execution_time', 3600);
        ini_set('memory_limit', '-1');

        foreach (TicketPerformer::all() as $performer)
        {
            $count = count($performer->all_productions ?? []);
            if ($count != $performer->events_count) 
                $performer->update(['events_count' => $count]);
        }
    }
}
