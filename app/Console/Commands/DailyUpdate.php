<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
        // $this->runDirectBonusIncomes();
        $this->runRecurringIncomes();
    }

    /**
     * Run direct bonus for recommended members
     * at next day when a recommender becomes member. 
     */
    private function runDirectBonusIncomes()
    {
        $date = new \DateTime();

        echo '[Direct Bonus Incomes]>>>> Starting on '. $date->format('Y-m-d'). "\n";

        $setting_direct_bonus = \App\Setting::where('setting_field', 'direct_bonus_income')->first();
        $setting_point_rate = \App\Setting::where('setting_field', 'point_rate')->first();

        if (!$setting_direct_bonus) {
            echo '[Direct Bonus Incomes]>>>> Error, failed for not found setting "Direct Bonus"'. "\n";
        } elseif(!$setting_point_rate) {
            echo '[Direct Bonus Incomes]>>>> Error, failed for not found setting "Point Rate"'. "\n";
        } else {
            $direct_bonus = floatval($setting_direct_bonus->value);
            $point_rate = floatval($setting_point_rate->value);
            $add_point = $direct_bonus * $point_rate * 0.01;

            $count = 0;
            $date->sub(new \DateInterval('P1D'));
            $members = \App\Member::whereDate('entry_date', '=', $date->format('Y-m-d'))->get();

            $members->each(function($member) use($direct_bonus, $add_point, $point_rate, &$count) {
                if ($member->refer) {
                    $referer = $member->refer->referer;
                    
                    $income = new \App\Income;
                    $income->member_id = $referer->id;
                    $income->old_amount = $referer->balance;
                    $income->new_amount = floatval($referer->balance) + $direct_bonus;
                    $income->direct_amount = $direct_bonus;
                    $income->type = \App\Type::INCOME_DIRECT_BONUS;
                    $income->note = 'Direct bonus for recommend by "'. $member->name. '"';
                    $income->save();

                    $point = new \App\Point;
                    $point->member_id = $referer->id;
                    $point->old_point = $referer->point;
                    $point->new_point = floatval($referer->point) + $add_point;
                    $point->type = \App\Type::POINT_INCOME;
                    $point->note = $point_rate.'% of incoming';
                    $point->save();

                    $referer->balance = floatval($referer->balance) + $direct_bonus;
                    $referer->point = floatval($referer->point) + $add_point;
                    $referer->save();

                    $count++;
                }
            });

            echo '[Direct Bonus Incomes]>>>> OK, done for '. $count. ' members'. "\n";
        }
    }

    /**
     * Run recurring for incoming periods
     */
    private function runRecurringIncomes()
    {
        $date = new \DateTime();

        echo '[Recurring Incomes]>>>> Starting on '. $date->format('Y-m-d'). "\n";

        $setting_recurring_income = \App\Setting::where('setting_field', 'recurring_income')->first();
        $setting_point_rate = \App\Setting::where('setting_field', 'point_rate')->first();
        $setting_recurring_income_rate = \App\Setting::where('setting_field', 'recurring_income_rate')->first();
        $setting_recurring_periods = \App\Setting::where('setting_field', 'recurring_periods')->first();

        if (!$setting_recurring_income) {
            echo '[Recurring Incomes]>>>> Error, failed for not found setting "Recurring Income"'. "\n";
        } elseif(!$setting_point_rate) {
            echo '[Recurring Incomes]>>>> Error, failed for not found setting "Point Rate"'. "\n";
        } elseif(!$setting_recurring_income_rate) {
            echo '[Recurring Incomes]>>>> Error, failed for not found setting "Recurring Income Rate"'. "\n";
        } elseif(!$setting_recurring_periods) {
            echo '[Recurring Incomes]>>>> Error, failed for not found setting "Recurring Periods"'. "\n";
        } else {
            $recurring_income = floatval($setting_recurring_income->value);
            $point_rate = floatval($setting_point_rate->value);
            $income_rate = floatval($setting_recurring_income_rate->value);
            $recurring_periods = intval($setting_recurring_periods->value);

            $count = 0;
            $members = \App\Member::whereDate('next_period_date', '=', $date->format('Y-m-d'))->get();
            $date->add(new \DateInterval('P7D'));

            $members->each(function($member) use ($recurring_income, $point_rate, $income_rate, $date, &$count) {
                $periods = intval($member->periods) + 1;
                $addtional_income = $income_rate * $member->balance * 0.01;
                $add_point = ($recurring_income + $addtional_income) * $point_rate * 0.01;

                $income = new \App\Income;
                $income->member_id = $member->id;
                $income->old_amount = $member->balance;
                $income->new_amount = floatval($member->balance) + $recurring_income + $addtional_income;
                $income->recurring_amount = $recurring_income + $addtional_income;
                $income->periods = $periods;
                if ($periods < $recurring_periods) {
                    $income->next_period_date = $date->format('Y-m-d');
                }
                $income->type = \App\Type::INCOME_RECURRING;
                $income->note = 'Recurring income periods:'. $periods;
                $income->save();

                $point = new \App\Point;
                $point->member_id = $member->id;
                $point->old_point = $member->point;
                $point->new_point = floatval($member->point) + $add_point;
                $point->type = \App\Type::POINT_INCOME;
                $point->note = $point_rate.'% of incoming';
                $point->save();

                $member->balance = floatval($member->balance) + $recurring_income + $addtional_income;
                $member->point = floatval($member->point) + $add_point;
                $member->periods = $periods;
                if ($periods < 7) {
                    $member->next_period_date = $date->format('Y-m-d');
                }
                $member->save();

                $count++;
            });

            echo '[Recurring Incomes]>>>> OK, done for '. $count. ' members'. "\n";
        }
    }
}