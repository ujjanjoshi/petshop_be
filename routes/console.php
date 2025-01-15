<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\FetchExperiences; // Import your command
use App\Console\Commands\SeedDB;
use App\Models\Experience;
use App\Models\ExperienceCategory;
use App\Models\ExperienceCity;
use App\Models\ExperienceCountry;
use App\Models\ExperienceLocation;
use App\Models\ExperiencesCategoriesCollections;
use App\Models\ExperienceState;
use App\Models\ExperinceCountry;
use App\Models\ExperinceState;
use App\Models\ExperinceCity;
use App\Models\TicketChildren;
use App\Models\TicketCategory;
use App\Models\TicketPerformer;
use App\Models\TicketVenue;
use App\Models\TicketProduction;
use App\Models\TicketProductionPerformer;
use App\Models\Hotel;
use App\Models\HotelDestination;
use App\Models\Ticket as ModelsTicket;
use App\Models\TicketDeliveryDetail;
use App\Models\TicketDetail;
use App\Models\TicketFaceValueDetail;
use App\Models\TicketNumberOfTicketsForSale;
use App\Models\TicketProceedPriceDetail;
use App\Models\TicketRestrictionsBenefitsDetail;
use App\Models\TicketSeatDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;
use PHPUnit\Framework\Attributes\Ticket;


Schedule::command('app:update-db')->dailyAt('04:00')
                                  ->appendOutputTo(storage_path("logs/update_db.log"));
