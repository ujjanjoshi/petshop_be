<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Scheduled Update 
 *
 */
class UpdateDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-db {--since=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull API Data to Update current DB';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('max_execution_time', 1800);
        ini_set('memory_limit', '-1');

        $since = $this->option('since');
        if ($since == null) {
            $since = now()->subday(1)->format('Y-m-d');
        }

        $this->log("UpdateDB ==> LastModified Since=". $since);

        // Experience
        $this->log("City");
        $this->call('app:fetch-experience-city-data');

        $this->log("Country");
        $this->call('app:fetch-experience-country-data');

        $this->log("State");
        $this->call('app:fetch-experience-state-data');

        $this->log("Experience-Categories");
        $this->call('app:fetch-experience-categories-data');

        $this->log("Experience");
        $this->call('app:fetch-experiences-data');

        // Ticket -- both tix/tevo
        $this->log("Ticket Category");
        $this->call('app:fetch-ticket-categories-data', ['--provider' => 'tix', '--since' => $since]);
        $this->call('app:fetch-ticket-categories-data', ['--provider' => 'tevo', '--since' => $since]);

        $this->log("Ticket Performer");
        $this->call('app:fetch-ticket-performer-data', ['--provider' => 'tix', '--since' => $since]);
        $this->call('app:fetch-ticket-performer-data', ['--provider' => 'tevo', '--since' => $since]);

        $this->log("Ticket Venue");
        $this->call('app:fetch-ticket-venues-data', ['--provider' => 'tix', '--since' => $since]);
        $this->call('app:fetch-ticket-venues-data', ['--provider' => 'tevo', '--since' => $since]);

        $this->log("Ticket Production");
        $this->call('app:fetch-ticket-production-data', ['--provider' => 'tix', '--since' => $since]);
        $this->call('app:fetch-ticket-production-data', ['--provider' => 'tevo', '--since' => $since]);

        $this->log("Update Ticket Production Count");
        $this->call('app:update-ticket-count');
        $this->call('app:update-ticket-featured');

        $this->log("Merchandise");
        $this->call('app:fetch-merchandise-categories');
        $this->call('app:fetch-merchandise');
        
        $this->log("Rental");
        $this->call('app:fetch-rentals', ['--since' => $since]);
//        $this->call('app:fetch-update-rental');
// 
//        $this->log("Tours");
//        $this->call('app:fetch-tour-attraction');
//        $this->call('app:fetch-tour-destinations');
    }
    protected function log($msg)
    {
        $this->info ($msg);
        \Log::channel(config('LOG_CHANNEL'))->info($msg);
    }
}
