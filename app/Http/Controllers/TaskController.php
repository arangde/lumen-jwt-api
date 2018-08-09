<?php

namespace App\Http\Controllers;

use Log;
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
            $date->add(new \DateInterval('P7D'));

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
                        $income->next_period_date = $date->format('Y-m-d');
                        $income->periods = 0;
                        $income->type = Type::INCOME_DIRECT_BONUS;
                        $income->refer_member_id = $member->id;
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
     * Run recurring for recommends incoming periods
     */
    public function recurringRecommendsIncomes()
    {
        $date = new \DateTime();

        echo '>>>> Starting on '. $date->format('Y-m-d H:i:s'). "\n";

        $setting_recurring_income = Setting::where('setting_field', 'recurring_income')->first();
        $setting_point_rate = Setting::where('setting_field', 'point_rate')->first();
        $setting_recurring_periods = Setting::where('setting_field', 'recurring_periods')->first();

        if (!$setting_recurring_income) {
            echo '>>>> Error, failed for not found setting "Recurring Income"'. "\n";
        } elseif(!$setting_point_rate) {
            echo '>>>> Error, failed for not found setting "Point Rate"'. "\n";
        } elseif(!$setting_recurring_periods) {
            echo '>>>> Error, failed for not found setting "Recurring Periods"'. "\n";
        } else {
            $recurring_income = floatval($setting_recurring_income->value);
            $point_rate = floatval($setting_point_rate->value);
            $recurring_periods = intval($setting_recurring_periods->value);

            $count = 0;
            $incomes = Income::where('next_period_date', 'like', $date->format('Y-m-d').'%')->get();
            $date->add(new \DateInterval('P7D'));

            $incomes->each(function($income) use ($recurring_income, $point_rate, $date, $recurring_periods, &$count) {
                echo '>>>> '. $income->member_id. ', '. $income->member->name. ', '. $income->periods. "\n";

                $periods = intval($income->periods) + 1;
                $add_point = $recurring_income * $point_rate * 0.01;

                $income2 = new Income;
                $income2->member_id = $income->member_id;
                $income2->old_amount = $income->member->balance;
                $income2->new_amount = floatval($income->member->balance) + $recurring_income;
                $income2->recurring_amount = $recurring_income;
                $income2->periods = $periods;
                if ($periods < $recurring_periods) {
                    $income2->next_period_date = $date->format('Y-m-d');
                }
                $income2->type = Type::INCOME_RECURRING;
                if ($income->referMember) {
                    $income2->refer_member_id = $income->referMember->id;
                    $income2->note = 'Recurring income for recommend by "'. $income->referMember->name. '", periods: '. $periods;
                } else {
                    $income2->note = 'Recurring income, periods: '. $periods;
                }
                $income2->save();

                $point = new Point;
                $point->member_id = $income->member_id;
                $point->old_point = $income->member->point;
                $point->new_point = floatval($income->member->point) + $add_point;
                $point->type = Type::POINT_INCOME;
                $point->note = $point_rate.'% of incoming';
                $point->save();

                $income->member->balance = floatval($income->member->balance) + $recurring_income;
                $income->member->point = floatval($income->member->point) + $add_point;
                $income->member->save();

                $count++;
            });

            echo '>>>> OK, done for '. $count. ' members'. "\n";
        }
    }
    
    /**
     * Run recurring for member incoming periods
     */
    public function recurringMemberIncomes()
    {
        $date = new \DateTime();

        echo '>>>> Starting on '. $date->format('Y-m-d H:i:s'). "\n";

        $setting_point_rate = Setting::where('setting_field', 'point_rate')->first();
        $setting_recurring_income_rate = Setting::where('setting_field', 'recurring_income_rate')->first();

        if(!$setting_point_rate) {
            echo '>>>> Error, failed for not found setting "Point Rate"'. "\n";
        } elseif(!$setting_recurring_income_rate) {
            echo '>>>> Error, failed for not found setting "Recurring Income Rate"'. "\n";
        } else {
            $point_rate = floatval($setting_point_rate->value);
            $income_rate = floatval($setting_recurring_income_rate->value);

            $count = 0;
            $members = Member::where('next_period_date', 'like', $date->format('Y-m-d').'%')
                             ->where('balance', '>', 0)->get();
            $date->add(new \DateInterval('P7D'));

            $members->each(function($member) use ($point_rate, $income_rate, $date, &$count) {
                echo '>>>> '. $member->id. ', '. $member->name. ', '. $member->entry_date. "\n";

                $add_income = $income_rate * $member->balance * 0.01;
                $add_point = $add_income * $point_rate * 0.01;

                $income = new Income;
                $income->member_id = $member->id;
                $income->old_amount = $member->balance;
                $income->new_amount = floatval($member->balance) + $add_income;
                $income->recurring_amount = $add_income;
                $income->type = Type::INCOME_RECURRING;
                $income->note = 'Recurring income for '. $income_rate.'% of balance';
                $income->next_period_date = $date->format('Y-m-d');
                $income->save();

                $point = new Point;
                $point->member_id = $member->id;
                $point->old_point = $member->point;
                $point->new_point = floatval($member->point) + $add_point;
                $point->type = Type::POINT_INCOME;
                $point->note = $point_rate.'% of incoming';
                $point->save();

                $member->balance = floatval($member->balance) + $add_income;
                $member->point = floatval($member->point) + $add_point;
                $member->next_period_date = $date->format('Y-m-d');
                $member->save();

                $count++;
            });

            echo '>>>> OK, done for '. $count. ' members'. "\n";
        }
    }

    public function addMember($data) {
        return false;
    }
    
    /**
     * Add calc members data manually
     */
    public function calcMembers() {
        try {
            echo '>>>> Clear tables'. "\n";
            Income::truncate();
            Point::truncate();

            $members = Member::limit(10)->get();
            $members->each(function($member) {
                $date = new \DateTime($member->entry_date);
                echo '>>>> '. $member->id. ' '. $member->name. '('. $member->username. ') refers '. $date->format('Y-m-d'). "\n";

                $member->referers->sortBy('entry_date')->each(function($refer) use($date, $member) {
                    if ($refer->member->id < 350) {
                        $setting_direct_bonus = Setting::where('setting_field', 'direct_bonus_income')->first();
                        $setting_point_rate = Setting::where('setting_field', 'point_rate')->first();
                        $setting_recurring_income = Setting::where('setting_field', 'recurring_income')->first();
                        $setting_recurring_periods = Setting::where('setting_field', 'recurring_periods')->first();
                        $setting_recurring_income_rate = Setting::where('setting_field', 'recurring_income_rate')->first();
            
                        $direct_bonus = intval($setting_direct_bonus->value);
                        $point_rate = intval($setting_point_rate->value);
                        $recurring_income = intval($setting_recurring_income->value);
                        $recurring_periods = intval($setting_recurring_periods->value);
                        $recurring_income_rate = intval($setting_recurring_income_rate->value);
            
                        $now = new \DateTime();
                        $date1 = new \DateTime($refer->member->entry_date);
                        echo '>>>>>>>> '. $refer->member->id. ' '. $refer->member->name. '('. $refer->member->username. ') reccurring '. $date1->format('Y-m-d'). "\n";

                        $interval = $date->diff($date1);
                        $diff_days = intval($interval->format('%R%a')) % 7;
                        $diff_days = ($diff_days + 7) % 7;
                        echo '$diff_days: '. $diff_days. "\n";
                        $interval1 = $date1->diff($now);
                        $periods = intval(intval($interval1->format('%a')) / 7) + 1;

                        for($i = 0; $i<$periods; $i++) {
                            if($i < $recurring_periods + 2) {
                                $balance = $i === 0 ? $direct_bonus : $recurring_income;
                                echo '>>>>>>>>>>>>>>>>income periods: '. $i. ' $'. $balance. " ". $date1->format('Y-m-d'). "\n";

                                $income = new Income;
                                $income->member_id = $member->id;
                                $income->old_amount = $member->balance;
                                $income->new_amount = floatval($member->balance) + $balance;
                                if ($i === 0) {
                                    $income->direct_amount = $balance;
                                    $income->type = Type::INCOME_DIRECT_BONUS;
                                    $income->note = 'Direct bonus for recommend by "'. $refer->member->name. '"';
                                } else {
                                    $income->recurring_amount = $balance;
                                    $income->type = Type::INCOME_RECURRING;
                                    $income->note = 'Recurring income for recommend by "'. $refer->member->name. '", periods: '. $i;
                                }
                                $income->refer_member_id = $refer->member_id;
                                $income->created_at = $date1->format('Y-m-d');

                                $date1->add(new \DateInterval('P7D'));
                                if($i < $recurring_periods + 1) {
                                    $income->next_period_date = $date1->format('Y-m-d');
                                }
                                $income->periods = $i;
                                $income->save();

                                $add_point = $balance * $point_rate * 0.01;
                                echo '>>>>>>>>>>>>>>>>point $'. $add_point. "\n";

                                $point = new Point;
                                $point->member_id = $member->id;
                                $point->old_point = $member->point;
                                $point->new_point = floatval($member->point) + $add_point;
                                $point->type = Type::POINT_INCOME;
                                $point->note = $point_rate.'% of incoming';
                                $point->created_at = $date1->format('Y-m-d');
                                $point->save();

                                $member->balance = floatval($member->balance) + $balance;
                                $member->point = floatval($member->point) + $add_point;
                                $member->save();

                                $date2 = clone $date1;
                                $date2->sub(new \DateInterval('P'. $diff_days. 'D'));
                                if ($date2 < $now) {
                                    $date3 = clone $date2;
                                    $date3->sub(new \DateInterval('P7D'));

                                    $add_income = $recurring_income_rate * $member->balance * 0.01;
                                    $add_point = $add_income * $point_rate * 0.01;
                                    echo '>>>>>>>>>>>>>>>>income '. $recurring_income_rate. '% $'. $add_income. " ". $date2->format('Y-m-d'). "\n";

                                    $income = new Income;
                                    $income->member_id = $member->id;
                                    $income->old_amount = $member->balance;
                                    $income->new_amount = floatval($member->balance) + $add_income;
                                    $income->recurring_amount = $add_income;
                                    $income->type = Type::INCOME_RECURRING;
                                    $income->note = 'Recurring income for '. $recurring_income_rate.'% of balance';
                                    $income->created_at = $date2->format('Y-m-d');
                                    $income->next_period_date = $date3->format('Y-m-d');
                                    $income->save();

                                    echo '>>>>>>>>>>>>>>>>point $'. $add_point. "\n";

                                    $point = new Point;
                                    $point->member_id = $member->id;
                                    $point->old_point = $member->point;
                                    $point->new_point = floatval($member->point) + $add_point;
                                    $point->type = Type::POINT_INCOME;
                                    $point->note = $point_rate.'% of incoming';
                                    $point->created_at = $date2->format('Y-m-d');
                                    $point->save();

                                    $member->balance = floatval($member->balance) + $add_income;
                                    $member->point = floatval($member->point) + $add_point;
                                    $member->next_period_date = $date3->format('Y-m-d');
                                    $member->save();
                                }
                            }
                        }
                    }
                });
            });
        } catch(\Exception $e) {
            print($e->getMessage(). "\n");
            print($e->getTraceAsString(). "\n");
        }
    }
}