<?php

namespace App\Http\Controllers;

use Validator;
use App\Member;
use App\Point;
use App\Income;
use App\Refer;
use App\Setting;
use App\Type;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use GenTux\Jwt\JwtToken;
use GenTux\Jwt\GetsJwtToken;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Routing\Controller as BaseController;

class MemberController extends BaseController 
{
    use GetsJwtToken;

    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function index() {
        $members = Member::all();
        return response()->json($members);
    }

    public function getProfile() {
        $token = $this->jwtToken();
        $id = $token->payload('context.id');
  
        $member = Member::with('referers', 'refer', 'incomes', 'points', 'withdrawals', 'sales', 'redeems')->find($id);
        if ($member) {
            $member->referers->each(function($refer) {
                $refer->load('member');
            });
            return response()->json($member);
        } else {
            return response(['error' => 'Member not found'], 404);
        }
    }

    public function saveProfile(Request $request) {
        $token = $this->jwtToken();
        $id = $token->payload('context.id');
  
        $member = Member::find($id);
        if ($member) {
            if($request->input('password')) {
                $member->password = app('hash')->make($request->input('password'));
            }
            $member->phone_number = $request->input('phone_number');
            $member->card_number = $request->input('card_number');
            $member->save();

            return response($member);
        } else {
          return response(['error' => 'Member not found'], 404);
        }
    }
    
    public function create(Request $request) {
        $this->validate($request, [
            'name' => 'required',
            'username' => 'required|unique:members',
            'password' => 'required'
        ]);

        $date = new \DateTime();
        $date->add(new \DateInterval('P7D'));

        $member = new Member;
        $member->name = $request->input('name');
        $member->username = $request->input('username');
        $member->password = app('hash')->make($request->input('password'));
        $member->phone_number = $request->input('phone_number');
        $member->card_number = $request->input('card_number');
        $member->entry_date = $request->input('entry_date') ? $request->input('entry_date') : date('Y-m-d');
        $member->next_period_date = $date->format('Y-m-d');
        $member->periods = 0;
        $member->save();

        if($request->input('refer_id')) {
            $refer_member = Member::find($request->input('refer_id'));
            if($refer_member) {
                $refer = new Refer;
                $refer->member_id = $member->id;
                $refer->refer_id = $refer_member->id;
                $refer->refer_name = $refer_member->name;
                $refer->save();

                $task = new TaskController;
                $task->referIncomes($refer_member);
            }
        }

        return response($member, 201);
    }

    public function get($id) {
        $member = Member::with('refer')->find($id);
        if($member) {
            return response($member);
        }
        else {
            return response(['error' => 'Not found member for ID '. $id], 404);
        }
    }

    public function update(Request $request, $id) {
        $member = Member::find($id);
        if($member) {
            if($request->input('password')) {
                $member->password = app('hash')->make($request->input('password'));
            }
            $member->phone_number = $request->input('phone_number');
            $member->card_number = $request->input('card_number');
            $member->save();

            return response($member);
        }
        else {
            return response(['error' => 'Not found member for ID '. $id], 404);
        }
    }

    public function delete($id) {
        $member = Member::find($id);
        if($member) {
            $member->delete();
            return response('Deleted Successfully');
        }
        else {
            return response(['error' => 'Not found member for ID '. $id], 404);
        }
    }

    public function getIncomes(Request $request, $id) {
        $member = Member::with('incomes')->find($id);
        if ($member) {
            return response()->json($member);
        } else {
            return response(['error' => 'Member not found'], 404);
        }
    }

    public function getWithdrawals(Request $request, $id) {
        $member = Member::with('withdrawals')->find($id);
        if ($member) {
            return response()->json($member);
        } else {
            return response(['error' => 'Member not found'], 404);
        }
    }

    public function getPoints(Request $request, $id) {
        $member = Member::with('points')->find($id);
        if ($member) {
            return response()->json($member);
        } else {
            return response(['error' => 'Member not found'], 404);
        }
    }

    public function getSales(Request $request, $id) {
        $member = Member::with('sales')->find($id);
        if ($member) {
            return response()->json($member);
        } else {
            return response(['error' => 'Member not found'], 404);
        }
    }

    public function getRefers(Request $request, $id) {
        $member = Member::with('referers')->find($id);
        if ($member) {
            $member->referers->each(function($refer) {
                $refer->load('member');
            });
            return response()->json($member);
        } else {
            return response(['error' => 'Member not found'], 404);
        }
    }

    public function getRedeems(Request $request, $id) {
        $member = Member::with('redeems')->find($id);
        if ($member) {
            return response()->json($member);
        } else {
            return response(['error' => 'Member not found'], 404);
        }
    }
    
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

        echo '[Direct Bonus Incomes]>>>> Starting on '. $date->format('Y-m-d'). "\n";

        $setting_direct_bonus = Setting::where('setting_field', 'direct_bonus_income')->first();
        $setting_point_rate = Setting::where('setting_field', 'point_rate')->first();

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
            $members = Member::whereDate('entry_date', '=', $date->format('Y-m-d'))->get();

            $members->each(function($member) use($direct_bonus, $add_point, $point_rate, &$count) {
                if ($member->refer) {
                    $referer = $member->refer->referer;
                    
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
            });

            echo '[Direct Bonus Incomes]>>>> OK, done for '. $count. ' members'. "\n";
        }
    }

    /**
     * Run recurring for incoming periods
     */
    public function recurringIncomes()
    {
        $date = new \DateTime();

        echo '[Recurring Incomes]>>>> Starting on '. $date->format('Y-m-d'). "\n";

        $setting_recurring_income = Setting::where('setting_field', 'recurring_income')->first();
        $setting_point_rate = Setting::where('setting_field', 'point_rate')->first();
        $setting_recurring_income_rate = Setting::where('setting_field', 'recurring_income_rate')->first();
        $setting_recurring_periods = Setting::where('setting_field', 'recurring_periods')->first();

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
            $members = Member::whereDate('next_period_date', '=', $date->format('Y-m-d'))->get();
            $date->add(new \DateInterval('P7D'));

            $members->each(function($member) use ($recurring_income, $point_rate, $income_rate, $date, &$count) {
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