<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AppMigrateProduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'devtool:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Startup Application Migrate for production';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        set_time_limit(300);
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('route:clear', ['--quiet' => true,]);
        $this->call('config:cache', ['--quiet' => true,]);
        $this->call('migrate', ['--force' => true,]);
        $this->info('Successfully Migration.');
    }
}
