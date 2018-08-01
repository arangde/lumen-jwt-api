<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MinuteUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'minute:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update in every minutes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('*** Task running [Add Setting] ***');

        $setting = new \App\Setting;
        $setting->setting_field = "addtion";
        $setting->value = "addtion_value";
        $setting->save();

        $this->info('*** Task finished [Add Setting] ***');
    }
}