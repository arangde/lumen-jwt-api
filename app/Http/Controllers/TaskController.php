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
    public function referIncomes($member) {
        $setting_recommends = array();

        for($i = 1; $i < 7; $i++) {
            $number = Setting::where('setting_field', 'recommends_number'. $i)->first();
            $rate = Setting::where('setting_field', 'recommends_rate'. $i)->first();

            if ($number && $rate) {
                $setting_recommends[] = array(
                    'number' => intval($number->value),
                    'rate' => intval($rate->value)
                );
            } else {
                return;
            }
        }
        
        $setting_point_rate = Setting::where('setting_field', 'point_rate')->first();

        if (!empty($setting_recommends) && $setting_point_rate) {
            $count = $member->referers->count();
            $recommends_reached = intval($member->recommends_reached);
            $rate = 0;

            for($i = 0; $i < count($setting_recommends); $i++) {
                if ($count === $setting_recommends[$i]['number'] && $recommends_reached < $setting_recommends[$i]['number']) {
                    $rate = $setting_recommends[$i]['rate'];
                    $recommends_reached = $setting_recommends[$i]['number'];
                }   
            }

            if ($rate > 0) {
                $sum = 0;
                $member->referers->each(function($refer) use(&$sum) {
                    $sum += floatval($refer->member->balance);
                });
                $refers_amount = $sum * $rate * 0.01;
                $add_point = $refers_amount * floatval($setting_point_rate->value) * 0.01;

                $income = new Income;
                $income->member_id = $member->id;
                $income->old_amount = $member->balance;
                $income->new_amount = floatval($member->balance) + $refers_amount;
                $income->refers_amount = $refers_amount;
                $income->type = Type::INCOME_REFERS_REACHED;
                $income->note = 'Recommends reached number:'. $count;
                $income->save();

                $point = new Point;
                $point->member_id = $member->id;
                $point->old_point = $member->point;
                $point->new_point = floatval($member->point) + $add_point;
                $point->type = Type::POINT_INCOME;
                $point->note = $setting_point_rate->value.'% of incoming';
                $point->save();

                $member->balance = floatval($member->balance) + $refers_amount;
                $member->point = floatval($member->point) + $add_point;
                $member->recommends_reached = $recommends_reached;
                $member->save();
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
            $incomes = Income::where('next_period_date', 'like', $date->format('Y-m-d').'%')
                             ->whereIn('type', [Type::INCOME_DIRECT_BONUS, Type::INCOME_RECURRING_RECOMMEND])
                             ->get();
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
                $income2->type = Type::INCOME_RECURRING_RECOMMEND;
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
            $members = Member::where('next_period_date', 'like', $date->format('Y-m-d').'%')->get();
            $date->add(new \DateInterval('P7D'));

            $members->each(function($member) use ($point_rate, $income_rate, $date, &$count) {
                if (floatval($member->balance) > 0) {
                    echo '>>>> '. $member->id. ', '. $member->name. ', '. $member->entry_date. "\n";

                    $add_income = $income_rate * $member->balance * 0.01;
                    $add_point = $add_income * $point_rate * 0.01;

                    $income = new Income;
                    $income->member_id = $member->id;
                    $income->old_amount = $member->balance;
                    $income->new_amount = floatval($member->balance) + $add_income;
                    $income->recurring_amount = $add_income;
                    $income->type = Type::INCOME_RECURRING_MEMBER;
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
                    $count++;
                }

                $member->next_period_date = $date->format('Y-m-d');
                $member->save();
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
            Member::where('id', '>', '0')->update(['point' => 0, 'balance' => 0, 'next_period_date' => '0000-00-00 00:00:00']);

            $members = Member::whereIn('id', [309, 314]);
            $members->each(function($member) {
                $entry_date = new \DateTime($member->entry_date);
                echo '>>>> '. $member->id. ' '. $member->name. '('. $member->username. ') '. $entry_date->format('Y-m-d'). "\n";

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

                echo '>>>>>>>> Adding incomes from refers'. "\n";

                $member->referers->each(function($refer) use($entry_date, $member, $direct_bonus, $point_rate, $recurring_income, $recurring_periods) {
                    $now = new \DateTime();
                    $income_date = new \DateTime($refer->member->entry_date);
                    
                    $interval = $entry_date->diff($income_date);
                    $diff_days = intval($interval->format('%r%a'));
                    if ($diff_days < 1)
                        return;
                    
                    echo '>>>>>>>> '. $refer->member->id. ' '. $refer->member->name. '('. $refer->member->username. ') reccurring '. $income_date->format('Y-m-d'). "\n";

                    $interval = $income_date->diff($now);
                    $periods = floor(intval($interval->format('%a')) / 7) + 1;

                    for($i = 0; $i<$periods; $i++) {
                        if($i < $recurring_periods + 1) {
                            $balance = $i === 0 ? $direct_bonus : $recurring_income;
                            $add_point = $balance * $point_rate * 0.01;
                            echo '>>>>>>>>>>>>>>>> income periods: '. $i. ' $'. $balance. " ". $income_date->format('Y-m-d'). "\n";

                            $first_create_date = clone $income_date;
                            $first_create_date->sub(new \DateInterval('P1D'));

                            $point = new Point;
                            $point->member_id = $member->id;
                            $point->new_point = $add_point;
                            $point->type = Type::POINT_INCOME;
                            $point->note = $point_rate.'% of incoming';
                            $point->created_at = $i===0 ? $first_create_date->format('Y-m-d') : $income_date->format('Y-m-d');
                            $point->save();

                            $income = new Income;
                            $income->member_id = $member->id;
                            if ($i === 0) {
                                $income->direct_amount = $balance;
                                $income->type = Type::INCOME_DIRECT_BONUS;
                                $income->note = 'Direct bonus for recommend by "'. $refer->member->name. '"';
                                $income->created_at = $first_create_date->format('Y-m-d');
                            } else {
                                $income->recurring_amount = $balance;
                                $income->type = Type::INCOME_RECURRING_RECOMMEND;
                                $income->note = 'Recurring income for recommend by "'. $refer->member->name. '", periods: '. $i;
                                $income->created_at = $income_date->format('Y-m-d');
                            }
                            $income->refer_member_id = $refer->member_id;

                            $income_date->add(new \DateInterval('P7D'));
                            if($i < $recurring_periods) {
                                $income->next_period_date = $income_date->format('Y-m-d');
                            }
                            $income->periods = $i;
                            $income->save();
                        }
                    }
                });

                echo '>>>>>>>> Added incomes '. $member->incomes->count(). "\n";

                if ($member->incomes->count() > 0) {
                    echo '>>>>>>>> Adding incomes from member reccurring'. "\n";

                    $total_incomes = 0.0;
                    $total_points = 0.0;
                    $period_date = null;
                    $last_period_date = null;
                    
                    $member->incomes->sortBy('created_at')->values()->each(function ($income) use($entry_date, $member, $point_rate, $recurring_income_rate, &$total_incomes, &$period_date, &$last_period_date) {
                        $income_date = new \DateTime($income->created_at);

                        if (!$period_date) {
                            $interval = $entry_date->diff($income_date);
                            $diff_days = intval($interval->format('%a'));
                            $diff_days = ceil($diff_days / 7) * 7;

                            $period_date = clone $entry_date;
                            $period_date->add(new \DateInterval('P'. $diff_days. 'D'));
                        }

                        while($period_date->format('Y-m-d') < $income_date->format('Y-m-d')) {
                            $next_period_date = clone $period_date;
                            $next_period_date->add(new \DateInterval('P7D'));

                            $add_income = $recurring_income_rate * $total_incomes * 0.01;
                            $add_point = $add_income * $point_rate * 0.01;

                            echo '>>>>>>>>>>>>>>>> income '. $recurring_income_rate. '% $'. $add_income. " ". $period_date->format('Y-m-d'). "\n";

                            $income2 = new Income;
                            $income2->member_id = $member->id;
                            $income2->old_amount = $total_incomes;
                            $income2->new_amount = $total_incomes + $add_income;
                            $income2->recurring_amount = $add_income;
                            $income2->type = Type::INCOME_RECURRING_MEMBER;
                            $income2->note = 'Recurring income for '. $recurring_income_rate.'% of balance';
                            $income2->created_at = $period_date->format('Y-m-d');
                            $income2->next_period_date = $next_period_date->format('Y-m-d');
                            $income2->save();

                            $point = new Point;
                            $point->member_id = $member->id;
                            $point->new_point = $add_point;
                            $point->type = Type::POINT_INCOME;
                            $point->note = $point_rate.'% of incoming';
                            $point->created_at = $period_date->format('Y-m-d');
                            $point->save();

                            $total_incomes += $add_income;
                            $period_date = clone $next_period_date;
                        }

                        if ($period_date->format('Y-m-d') >= $income_date->format('Y-m-d')) {
                            echo '>>>>>>>>>>>>>>>> income update('. $income->refer_member_id. ') $'. ($income->direct_amount + $income->recurring_amount). " ". $income_date->format('Y-m-d'). "\n";

                            $income->old_amount = $total_incomes;
                            $income->new_amount = $total_incomes + $income->direct_amount + $income->recurring_amount;
                            $income->save();

                            $total_incomes += $income->direct_amount + $income->recurring_amount;
                        }

                        if (!$last_period_date || $last_period_date->format('Y-m-d') < $period_date->format('Y-m-d'))
                            $last_period_date = clone $period_date;
                    });

                    echo '>>>>>>>> Updating points'. "\n";

                    $member->points->sortBy('created_at')->values()->each(function ($point) use(&$total_points) {
                        $add_point = $point->new_point;

                        $point->old_point = $total_points;
                        $point->new_point = $total_points + $point->new_point;
                        $point->save();

                        $total_points += $add_point;
                    });

                    if ($last_period_date && $last_period_date->format('Y-m-d') < date('Y-m-d')) {
                        $period_date = clone $last_period_date;
                        // $period_date->add(new \DateInterval('P7D'));
                        
                        echo '>>>>>>>> Recurring after recommends '. $period_date->format('Y-m-d'). "\n";

                        while($period_date->format('Y-m-d') < date('Y-m-d')) {
                            $next_period_date = clone $period_date;
                            $next_period_date->add(new \DateInterval('P7D'));

                            $add_income = $recurring_income_rate * $total_incomes * 0.01;
                            $add_point = $add_income * $point_rate * 0.01;

                            echo '>>>>>>>>>>>>>>>> income '. $recurring_income_rate. '% $'. $add_income. " ". $period_date->format('Y-m-d'). "\n";

                            $income2 = new Income;
                            $income2->member_id = $member->id;
                            $income2->old_amount = $total_incomes;
                            $income2->new_amount = $total_incomes + $add_income;
                            $income2->recurring_amount = $add_income;
                            $income2->type = Type::INCOME_RECURRING_MEMBER;
                            $income2->note = 'Recurring income for '. $recurring_income_rate.'% of balance';
                            $income2->created_at = $period_date->format('Y-m-d');
                            $income2->next_period_date = $next_period_date->format('Y-m-d');
                            $income2->save();

                            $point = new Point;
                            $point->member_id = $member->id;
                            $point->old_point = $total_points;
                            $point->new_point = $total_points + $add_point;
                            $point->type = Type::POINT_INCOME;
                            $point->note = $point_rate.'% of incoming';
                            $point->created_at = $period_date->format('Y-m-d');
                            $point->save();

                            $total_incomes += $add_income;
                            $total_points += $add_point;
                            $period_date = clone $next_period_date;
                        }
                    }

                    $member->balance = $total_incomes;
                    $member->point = $total_points;
                    $member->next_period_date = $period_date->format('Y-m-d');
                } else {
                    $interval = $entry_date->diff(new \DateTime());
                    $diff_days = intval($interval->format('%a'));
                    $diff_days = ceil($diff_days / 7) * 7;
                    
                    $period_date = clone $entry_date;
                    $period_date->add(new \DateInterval('P'. $diff_days. 'D'));
                    $member->next_period_date = $period_date->format('Y-m-d');
                }

                $member->save();
            });
        } catch(\Exception $e) {
            print($e->getMessage(). "\n");
            print($e->getTraceAsString(). "\n");
        }
    }
}