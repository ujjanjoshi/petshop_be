<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TicketPerformer;
use App\Models\TicketCategory;
use App\Models\TicketVenue;

use App\Models\TicketProduction;
use App\Models\FeatureTicket;

class UpdateTicketFeatured extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-ticket-featured';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Featured Ticket/Production  based on popularity score';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('max_execution_time', 3600);
        ini_set('memory_limit', '-1');


        foreach(FeatureTicket::all() as $ticket) {
            $ticket->delete();
        }


        $sportIds   = TicketCategory::where('category_id', 1)->orWhere('parent_id', 1)->pluck('category_id')->toArray();
        $concertIds = TicketCategory::where('category_id', 54)->orWhere('parent_id', 54)->pluck('category_id')->toArray();
        $theaterIds = TicketCategory::where('category_id', 68)->orWhere('parent_id', 68)->pluck('category_id')->toArray();
        /* total 20 featured product */
        $featuredSports = TicketProduction::whereIn('category_id', $sportIds)
                                          ->orderBy('popularity_score', 'desc')
                                          ->take(10)
                                          ->get();
        foreach ($featuredSports as $production) {
            $feature = new FeatureTicket;
            $feature->ticket_id = $production->id;
            $feature->save();
        }
        $featuredConcerts= TicketProduction::whereIn('category_id', $concertIds)
                                          ->orderBy('popularity_score', 'desc')
                                          ->take(5)
                                          ->get();
        foreach ($featuredConcerts as $production) {
            $feature = new FeatureTicket;
            $feature->ticket_id = $production->id;
            $feature->save();
        }
        $featuredTheaters= TicketProduction::whereIn('category_id', $theaterIds)
                                          ->orderBy('popularity_score', 'desc')
                                          ->take(5)
                                          ->get();
        foreach ($featuredTheaters as $production) {
            $feature = new FeatureTicket;
            $feature->ticket_id = $production->id;
            $feature->save();
        }
    }
}
