<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SeedDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull API Data to seed the db';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('max_execution_time', 1800);
        ini_set('memory_limit', '-1');

        $this->call('db:seed');

        // $this->info("Branding Seeder");
        // $this->call('db:seed', ["--class" => "BrandingSeeder"]);

        //$this->info("Country Seeder");
        //$this->call('db:seed', ["--class" => "CountriesSeeder"]);

        // $this->info("SuperAdmin  Seeder");
        // $this->call('db:seed', ["--class" => "AdminSeeder"]);

        //$this->info("Hotel Facilities  Seeder");
        //$this->call('db:seed', ["--class" => "FacilitiesSeeder"]);

        $this->info("City");
        $this->call('app:fetch-experience-city-data');

        $this->info("Country");
        $this->call('app:fetch-experience-country-data');

        $this->info("State");
        $this->call('app:fetch-experience-state-data');

        $this->info("Experience-Categories");
        $this->call('app:fetch-experience-categories-data');

        $this->info("Experience");
        $this->call('app:fetch-experiences-data');

        $this->info("Ticket Category");
        $this->call('app:fetch-ticket-categories-data');

        $this->info("Ticket Performer");
        $this->call('app:fetch-ticket-performer-data');

        $this->info("Ticket Production");
        $this->call('app:fetch-ticket-production-data');

        $this->info("Ticket Venue");
        $this->call('app:fetch-ticket-venues-data');

        $this->info("Hotel");
        $this->call('app:fetch-hotel-destination-data');

        $this->info("Merchandise");
        $this->call('app:fetch-merchandise-categories');
        $this->call('app:fetch-merchandise');
        
        $this->info("Rental");
        $this->call('app:fetch-rentals');

        $this->info("Tours");
        $this->call('app:fetch-tour-attraction');
        $this->call('app:fetch-tour-destinations');

        /*
        $this->info("Hotels");
        $this->call('app:fetch-hotels-data');
         */
    }
}
