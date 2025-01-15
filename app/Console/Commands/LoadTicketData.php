<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LoadTicketData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:load-ticket-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull Ticket Data to seed the db';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('max_execution_time', 7200);
        ini_set('memory_limit', '-1');

        $this->info("Ticket Category");
        $this->call('app:fetch-ticket-categories-data', ['--provider' => 'tix']);
        $this->call('app:fetch-ticket-categories-data');

        $this->info("Ticket Performer");
        $this->call('app:fetch-ticket-performer-data', ['--provider' => 'tix']);
        $this->call('app:fetch-ticket-performer-data');

        $this->info("Ticket Venue");
        $this->call('app:fetch-ticket-venues-data', ['--provider' => 'tix']);
        $this->call('app:fetch-ticket-venues-data');

        $this->info("Ticket Production -- Merge");
        $this->call('app:fetch-ticket-production-data', ['--provider' => 'tix']);
        $this->call('app:fetch-ticket-production-data');
    }
}
