<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AppMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'devtool:freshmigrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Startup Application Migrate';

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
        if (app()->isLocal()) {
            $this->call('route:clear', ['--quiet' => true]);
            $this->call('config:cache', ['--quiet' => true]);
            $this->call('key:generate');
            $this->call('migrate:fresh');
            $this->call('db:seed');
            $this->call('config:clear', ['--quiet' => true]);
            $this->info('Successful Migration.');
        }
    }
}
