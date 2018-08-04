<?php

namespace App\Http\Controllers;

use Validator;
use App\Member;
use App\Point;
use App\Income;
use App\Refer;
use App\Setting;
use App\Type;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Routing\Controller as BaseController;

class TaskController extends BaseController 
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Calc recommender's balance and point
     */
    public function referIncomes($refer_member) {
        $setting_recommends_number_low = Setting::where('setting_field', 'recommends_number_low')->first();
        $setting_recommends_rate_low = Setting::where('setting_field', 'recommends_rate_low')->first();
        $setting_recommends_number_high = Setting::where('setting_field', 'recommends_number_high')->first();
        $setting_recommends_rate_high = Setting::where('setting_field', 'recommends_rate_high')->first();
        $setting_point_rate = Setting::where('setting_field', 'point_rate')->first();

        if ($setting_point_rate && $setting_recommends_number_low && $setting_recommends_rate_low
            && $setting_recommends_number_high && $setting_recommends_rate_high
        ) {
            $count = $refer_member->referers->count();
            $recommends_reached = intval($refer_member->recommends_reached);
            $recommends_number_low = intval($setting_recommends_number_low->value);
            $recommends_number_high = intval($setting_recommends_number_high->value);
            $rate = 0;

            if ($count === $recommends_number_low && $recommends_reached < $recommends_number_low) {
                $rate = intval($setting_recommends_rate_low->value);
                $recommends_reached = $recommends_number_low;
            }
            
            if ($count === $recommends_number_high && $recommends_reached < $recommends_number_high) {
                $rate = intval($setting_recommends_rate_high->value);
                $recommends_reached = $recommends_number_high;
            }

            if ($rate > 0) {
                $sum = 0;
                $refer_member->referers->each(function($refer) use(&$sum) {
                    $sum += floatval($refer->member->balance);
                });
                $refers_amount = $sum * $rate * 0.01;
                $add_point = $refers_amount * floatval($setting_point_rate->value) * 0.01;

                $income = new Income;
                $income->member_id = $refer_member->id;
                $income->old_amount = $refer_member->balance;
                $income->new_amount = floatval($refer_member->balance) + $refers_amount;
                $income->refers_amount = $refers_amount;
                $income->type = Type::INCOME_REFERS;
                $income->note = 'Recommends reached number:'. $count;
                $income->save();

                $point = new Point;
                $point->member_id = $refer_member->id;
                $point->old_point = $refer_member->point;
                $point->new_point = floatval($refer_member->point) + $add_point;
                $point->type = Type::POINT_INCOME;
                $point->note = $setting_point_rate->value.'% of incoming';
                $point->save();

                $refer_member->balance = floatval($refer_member->balance) + $refers_amount;
                $refer_member->point = floatval($refer_member->point) + $add_point;
                $refer_member->recommends_reached = $recommends_reached;
                $refer_member->save();
            }
        }
    }
    
    /**
     * Run direct bonus for recommended members
     * at next day when a recommender becomes member. 
     */
    public function directBonusIncomes()
    {
        $date = new \DateTime();

        echo '>>>> Starting on '. $date->format('Y-m-d H:i:s'). "\n";

        $setting_direct_bonus = Setting::where('setting_field', 'direct_bonus_income')->first();
        $setting_point_rate = Setting::where('setting_field', 'point_rate')->first();

        if (!$setting_direct_bonus) {
            echo '>>>> Error, failed for not found setting "Direct Bonus"'. "\n";
        } elseif(!$setting_point_rate) {
            echo '>>>> Error, failed for not found setting "Point Rate"'. "\n";
        } else {
            $direct_bonus = floatval($setting_direct_bonus->value);
            $point_rate = floatval($setting_point_rate->value);
            $add_point = $direct_bonus * $point_rate * 0.01;

            $count = 0;
            $date->sub(new \DateInterval('P1D'));
            $members = Member::where('entry_date', 'like', $date->format('Y-m-d').'%')->get();

            $members->each(function($member) use($direct_bonus, $add_point, $point_rate, &$count) {
                try {
                    if ($member->refer) {
                        $referer = $member->refer->referer;
                        
                        echo '>>>> '. $member->id. ', '. $member->entry_date. ', '. $member->name. ', '. $referer->id. ', '. $referer->name. "\n";
                        
                        $income = new Income;
                        $income->member_id = $referer->id;
                        $income->old_amount = $referer->balance;
                        $income->new_amount = floatval($referer->balance) + $direct_bonus;
                        $income->direct_amount = $direct_bonus;
                        $income->type = Type::INCOME_DIRECT_BONUS;
                        $income->note = 'Direct bonus for recommend by "'. $member->name. '"';
                        $income->save();

                        $point = new Point;
                        $point->member_id = $referer->id;
                        $point->old_point = $referer->point;
                        $point->new_point = floatval($referer->point) + $add_point;
                        $point->type = Type::POINT_INCOME;
                        $point->note = $point_rate.'% of incoming';
                        $point->save();

                        $referer->balance = floatval($referer->balance) + $direct_bonus;
                        $referer->point = floatval($referer->point) + $add_point;
                        $referer->save();

                        $count++;
                    }
                } catch(\Exception $e) {
                    print($e->getMessage());
                    die();
                }
            });

            echo '>>>> OK, done for '. $count. ' members'. "\n";
        }
    }

    /**
     * Run recurring for incoming periods
     */
    public function recurringIncomes()
    {
        $date = new \DateTime();

        echo '>>>> Starting on '. $date->format('Y-m-d H:i:s'). "\n";

        $setting_recurring_income = Setting::where('setting_field', 'recurring_income')->first();
        $setting_point_rate = Setting::where('setting_field', 'point_rate')->first();
        $setting_recurring_income_rate = Setting::where('setting_field', 'recurring_income_rate')->first();
        $setting_recurring_periods = Setting::where('setting_field', 'recurring_periods')->first();

        if (!$setting_recurring_income) {
            echo '>>>> Error, failed for not found setting "Recurring Income"'. "\n";
        } elseif(!$setting_point_rate) {
            echo '>>>> Error, failed for not found setting "Point Rate"'. "\n";
        } elseif(!$setting_recurring_income_rate) {
            echo '>>>> Error, failed for not found setting "Recurring Income Rate"'. "\n";
        } elseif(!$setting_recurring_periods) {
            echo '>>>> Error, failed for not found setting "Recurring Periods"'. "\n";
        } else {
            $recurring_income = floatval($setting_recurring_income->value);
            $point_rate = floatval($setting_point_rate->value);
            $income_rate = floatval($setting_recurring_income_rate->value);
            $recurring_periods = intval($setting_recurring_periods->value);

            $count = 0;
            $members = Member::where('next_period_date', 'like', $date->format('Y-m-d').'%')->get();
            $date->add(new \DateInterval('P7D'));

            $members->each(function($member) use ($recurring_income, $point_rate, $income_rate, $date, $recurring_periods, &$count) {
                echo '>>>> '. $member->id. ', '. $member->next_period_date. ', '. $member->name. "\n";

                $periods = intval($member->periods) + 1;
                $addtional_income = $income_rate * $member->balance * 0.01;
                $add_point = ($recurring_income + $addtional_income) * $point_rate * 0.01;

                $income = new Income;
                $income->member_id = $member->id;
                $income->old_amount = $member->balance;
                $income->new_amount = floatval($member->balance) + $recurring_income + $addtional_income;
                $income->recurring_amount = $recurring_income + $addtional_income;
                $income->periods = $periods;
                if ($periods < $recurring_periods) {
                    $income->next_period_date = $date->format('Y-m-d');
                }
                $income->type = Type::INCOME_RECURRING;
                $income->note = 'Recurring income periods:'. $periods;
                $income->save();

                $point = new Point;
                $point->member_id = $member->id;
                $point->old_point = $member->point;
                $point->new_point = floatval($member->point) + $add_point;
                $point->type = Type::POINT_INCOME;
                $point->note = $point_rate.'% of incoming';
                $point->save();

                $member->balance = floatval($member->balance) + $recurring_income + $addtional_income;
                $member->point = floatval($member->point) + $add_point;
                $member->periods = $periods;
                if ($periods < $recurring_periods) {
                    $member->next_period_date = $date->format('Y-m-d');
                }
                $member->save();

                $count++;
            });

            echo '>>>> OK, done for '. $count. ' members'. "\n";
        }
    }
    
    /**
     * Add member manually
     */
    public function addMember($data) {
        try {
            $setting_recurring_periods = Setting::where('setting_field', 'recurring_periods')->first();
            $recurring_periods = intval($setting_recurring_periods->value);
            
            $periods = isset($data['periods']) ? intval($data['periods']) : 0;

            $member = new Member;
            $member->name = $data['name'];
            $member->username = $data['username'];
            $member->password = app('hash')->make($data['password']);
            $member->phone_number = $data['phone_number'];
            $member->card_number = $data['card_number'];
            $member->entry_date = $data['entry_date'];
            $member->point = $data['point'];
            $member->balance = $data['balance'];
            $member->periods = $periods;
            if ($periods < $recurring_periods) {
                $member->next_period_date = $data['next_period_date'];
            }
            $member->recommends_reached = isset($data['recommends_reached']) ? intval($data['recommends_reached']) : 0;
            $member->save();

            if($data['refer_id']) {
                $refer_member = Member::find($data['refer_id']);
                if($refer_member) {
                    $refer = new Refer;
                    $refer->member_id = $member->id;
                    $refer->refer_id = $refer_member->id;
                    $refer->refer_name = $refer_member->name;
                    $refer->save();
                }
            }

            if ($data['balance']) {
                $income = new Income;
                $income->member_id = $member->id;
                $income->old_amount = 0;
                $income->new_amount = floatval($data['balance']);
                $income->direct_amount = floatval($data['balance']);
                $income->periods = $periods;
                if ($periods < $recurring_periods) {
                    $income->next_period_date = $data['next_period_date'];
                }
                $income->type = Type::INCOME_DIRECT;
                $income->note = 'Direct incomes';
                $income->save();
            }

            if ($data['point']) {
                $point = new Point;
                $point->member_id = $member->id;
                $point->old_point = 0;
                $point->new_point = floatval($data['point']);
                $point->type = Type::POINT_DIRECT;
                $point->note = 'Direct points';
                $point->save();
            }

            return $member;
        } catch(\Exception $e) {
            // print($e->getMessage());
            return false;
        }
    }
}