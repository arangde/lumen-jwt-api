<?php

namespace App\Http\Controllers;

use Validator;
use App\Member;
use App\Point;
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
        $email = $token->payload('context.email');
  
        $member = Member::with('refers', 'incomes', 'points', 'withdrawals', 'sales')->where("email", "=", $email)->first();
        if ($member) {
            return response()->json($member);
        } else {
            return response(['error' => 'Member not found'], 404);
        }
    }

    public function saveProfile(Request $request) {
        $token = $this->jwtToken();
        $email = $token->payload('context.email');
  
        $member = Member::where("email", "=", $email)->first();
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
            'email' => 'required|email|unique:members',
            'password' => 'required'
        ]);

        $member = new Member;
        $member->name = $request->input('name');
        $member->email = $request->input('email');
        $member->password = app('hash')->make($request->input('password'));
        $member->phone_number = $request->input('phone_number');
        $member->card_number = $request->input('card_number');
        $member->entry_date = $request->input('entry_date');
        $member->save();
        
        return response($member, 201);
    }

    public function get($id) {
        $member = Member::find($id);
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

    public function changePoint(Request $request, $id) {
        $member = Member::find($id);
        if($member) {
            $this->validate($request, [
                'point' => 'required',
            ]);
            
            $point = new Point;
            $point->member_id = $id;
            $point->old_point = $member->point;
            $point->new_amount = $member->point - $request->input('point');
            $point->note = $request->input('note');
            $point->save();

            $member->point = $member->point - $request->input('point');
            $member->save();

            return response($member);
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
}