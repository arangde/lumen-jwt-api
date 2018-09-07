<?php

namespace App\Http\Controllers;

use Validator;
use App\Withdrawal;
use App\Status;
use App\Type;
use App\Member;
use App\Income;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use GenTux\Jwt\GetsJwtToken;

class WithdrawalController extends BaseController 
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
        $withdrawals = Withdrawal::with('member')->get();
        return response()->json($withdrawals);
    }
    
    public function create(Request $request) {
        $this->validate($request, [
            'member_id' => 'required',
            'amount' => 'required',
        ]);

        $lastWithdrawal = Withdrawal::where('member_id', $request->input('member_id'))->orderBy('created_at', 'desc')->first();
        if ($lastWithdrawal) {
            $now = new \DateTime();
            $now_day = intval($now->format('z')) - intval($now->format('N'));
            $now_year = intval($now->format('Y'));

            $created_at = new \DateTime($lastWithdrawal->created_at);
            $created_day = intval($created_at->format('z')) - intval($created_at->format('N'));
            $created_year = intval($created_at->format('Y'));

            if ($now_year > $created_year) {
                $now_day += 365;
            }
            if($now_day <= $created_day) {
                return response(['error' => __('Members can only withdraw once a week.')], 404);
            }
        }

        $withdrawal = new Withdrawal;
        $withdrawal->member_id = $request->input('member_id');
        $withdrawal->amount = $request->input('amount');
        $withdrawal->status = Status::WITHDRAWAL_REQUESTED;
        $withdrawal->note = $request->input('note');
        $withdrawal->save();

        return response()->json($withdrawal, 201);
    }

    public function get($id) {
        $withdrawal = Withdrawal::with('member')->find($id);
        if($withdrawal) {
            $payload = $this->jwtPayload();
            if(isset($payload['context']['permission']) && $payload['context']['permission'] === 'member') {
                if($payload['context']['id'] === $withdrawal->member_id) {
                    return response($withdrawal);
                } else {
                    return response(['error' => __('You have not permission.')], 401);
                }
            } else {
                return response($withdrawal);
            }
        }
        else {
            return response(['error' => __('Not found data for #:ID', ['ID' => $id])], 404);
        }
    }

    public function update(Request $request, $id) {
        $withdrawal = Withdrawal::find($id);
        if($withdrawal) {
            $this->validate($request, [
                'member_id' => 'required',
                'amount' => 'required',
            ]);
            
            $withdrawal = Withdrawal::update($request->all());

            return response()->json($withdrawal);
        }
        else {
            return response(['error' => __('Not found data for #:ID', ['ID' => $id])], 404);
        }
    }

    public function delete($id) {
        $withdrawal = Withdrawal::find($id);
        if($withdrawal) {
            $withdrawal->delete();
            return response('Deleted Successfully');
        }
        else {
            return response(['error' => __('Not found data for #:ID', ['ID' => $id])], 404);
        }
    }

    public function accept(Request $request, $id) {
        $withdrawal = Withdrawal::with('member')->find($id);
        if($withdrawal) {
            $withdrawal->status = Status::WITHDRAWAL_ACCEPTED;
            $withdrawal->accepted_date = date('Y:m:d H:i:s');
            $withdrawal->save();

            $amount = $withdrawal->member->balance;
            $amount -= $withdrawal->amount;

            $income = new Income;
            $income->member_id = $withdrawal->member_id;
            $income->old_amount = $withdrawal->member->balance;
            $income->new_amount = $amount;
            $income->direct_amount = -1 * $withdrawal->amount;
            $income->type = Type::INCOME_WITHDRAWAL;
            $income->note = __('Withdrawal by #:ID', ['ID' => $withdrawal->id]);
            $income->save();

            $withdrawal->member->balance = $amount;
            $withdrawal->member->save();

            return response()->json($withdrawal);
        }
        else {
            return response(['error' => __('Not found data for #:ID', ['ID' => $id])], 404);
        }
    }

    public function reject(Request $request, $id) {
        $withdrawal = Withdrawal::with('member')->find($id);
        if($withdrawal) {
            $this->validate($request, [
                'reject_reason' => 'required',
            ]);
            
            $withdrawal->status = Status::WITHDRAWAL_REJECTED;
            $withdrawal->rejected_date = date('Y:m:d H:i:s');
            $withdrawal->reject_reason = $request->input('reject_reason');
            $withdrawal->save();

            return response()->json($withdrawal);
        }
        else {
            return response(['error' => __('Not found data for #:ID', ['ID' => $id])], 404);
        }
    }
}