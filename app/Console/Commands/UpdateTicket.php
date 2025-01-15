<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Scheduled Update 
 *
 */
class UpdateTicket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-ticket-db {--since=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull API Data to Update current Ticket DB';

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

    }
    protected function log($msg)
    {
        $this->info ($msg);
        \Log::channel(config('LOG_CHANNEL'))->info($msg);
    }
}
