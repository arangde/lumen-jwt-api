<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\TaskController;

class CalcMembers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calc:members';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'calculate members incomes and points';

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
        $this->info('*** Task running [Calc Members] ***');

        $task = new TaskController;
        $task->calcMembers();

        $this->info('*** Task finished [Calc Members] ***');
    }
}