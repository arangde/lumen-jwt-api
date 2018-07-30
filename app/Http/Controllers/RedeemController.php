<?php

namespace App\Http\Controllers;

use Validator;
use App\Redeem;
use App\Status;
use App\Type;
use App\Member;
use App\Point;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class RedeemController extends BaseController 
{
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
        $redeems = Redeem::with('member')->get();
        return response()->json($redeems);
    }
    
    public function create(Request $request) {
        $this->validate($request, [
            'member_id' => 'required',
            'point' => 'required',
            'note' => 'required',
        ]);

        $redeem = new Redeem;
        $redeem->member_id = $request->input('member_id');
        $redeem->point = $request->input('point');
        $redeem->status = Status::REDEEM_REQUESTED;
        $redeem->note = $request->input('note');
        $redeem->save();

        return response()->json($redeem, 201);
    }

    public function get($id) {
        $redeem = Redeem::with('member')->find($id);
        if($redeem) {
            return response($redeem);
        }
        else {
            return response(['error' => 'Not found redeem for ID '. $id], 404);
        }
    }

    public function accept(Request $request, $id) {
        $redeem = Redeem::with('member')->find($id);
        if($redeem) {
            $redeem->status = Status::REDEEM_ACCEPTED;
            $redeem->accepted_date = date('Y:m:d H:i:s');
            $redeem->save();

            $member_point = $redeem->member->point;
            $member_point += $redeem->point;

            $point = new Point;
            $point->member_id = $redeem->member_id;
            $point->old_point = $redeem->member->point;
            $point->new_point = $member_point;
            $point->type = Type::POINT_REDEEM;
            $point->note = 'Redeem by ID '. $redeem->id;
            $point->save();

            $redeem->member->point = $member_point;
            $redeem->member->save();

            return response()->json($redeem);
        }
        else {
            return response(['error' => 'Not found redeem for ID '. $id], 404);
        }
    }

    public function reject(Request $request, $id) {
        $redeem = Redeem::with('member')->find($id);
        if($redeem) {
            $this->validate($request, [
                'reject_reason' => 'required',
            ]);
            
            $redeem->status = Status::REDEEM_REJECTED;
            $redeem->rejected_date = date('Y:m:d H:i:s');
            $redeem->reject_reason = $request->input('reject_reason');
            $redeem->save();

            return response()->json($redeem);
        }
        else {
            return response(['error' => 'Not found redeem for ID '. $id], 404);
        }
    }
}