<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\TaskController;

class DailyUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update members balance and points daily';

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
        $task = new TaskController;

        $this->info('****** Task run [Direct Bonus Incomes] ******');
        $task->directBonusIncomes();
        $this->info('****** Task end [Direct Bonus Incomes] ******');

        $this->info('****** Task run [Recurring Recommends Incomes] ******');
        $task->recurringRecommendsIncomes();
        $this->info('****** Task end [Recurring Recommends Incomes] ******');

        $this->info('****** Task run [Recurring Member Incomes] ******');
        $task->recurringMemberIncomes();
        $this->info('****** Task end [Recurring Member Incomes] ******');
    }
}