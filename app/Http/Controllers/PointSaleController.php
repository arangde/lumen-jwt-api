<?php

namespace App\Http\Controllers;

use Validator;
use App\PointSale;
use App\Status;
use App\Type;
use App\Member;
use App\Item;
use App\Point;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use GenTux\Jwt\GetsJwtToken;

class PointSaleController extends BaseController 
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
        $pointSales = PointSale::with('member', 'item')->get();
        return response()->json($pointSales);
    }
    
    public function create(Request $request) {
        $this->validate($request, [
            'member_id' => 'required',
            'item_id' => 'required',
            'point' => 'required',
            'note' => 'required',
        ]);

        $pointSale = new PointSale;
        $pointSale->member_id = $request->input('member_id');
        $pointSale->item_id = $request->input('item_id');
        $pointSale->point = $request->input('point');
        $pointSale->status = Status::POINT_SALE_REQUESTED;
        $pointSale->note = $request->input('note');
        $pointSale->save();
        $pointSale->load('member', 'item');

        return response()->json($pointSale, 201);
    }

    public function get($id) {
        $pointSale = PointSale::with('member', 'item')->find($id);
        if($pointSale) {
            $payload = $this->jwtPayload();
            if(isset($payload['context']['permission']) && $payload['context']['permission'] === 'member') {
                if($payload['context']['id'] === $pointSale->member_id) {
                    return response($pointSale);
                } else {
                    return response(['error' => 'You have not permission.'], 401);
                }
            } else {
                return response($pointSale);
            }
        }
        else {
            return response(['error' => 'Not found data for ID '. $id], 404);
        }
    }

    public function accept(Request $request, $id) {
        $pointSale = PointSale::with('member', 'item')->find($id);
        if($pointSale) {
            $pointSale->status = Status::POINT_SALE_ACCEPTED;
            $pointSale->accepted_date = date('Y:m:d H:i:s');
            $pointSale->save();

            $member_point = $pointSale->member->point;
            $member_point -= $pointSale->point;

            $point = new Point;
            $point->member_id = $pointSale->member_id;
            $point->old_point = $pointSale->member->point;
            $point->new_point = $member_point;
            $point->type = Type::POINT_SALE;
            $point->note = 'PointSale by ID '. $pointSale->id;
            $point->save();

            $pointSale->member->point = $member_point;
            $pointSale->member->save();

            return response()->json($pointSale);
        }
        else {
            return response(['error' => 'Not found data for ID '. $id], 404);
        }
    }

    public function reject(Request $request, $id) {
        $pointSale = PointSale::with('member', 'item')->find($id);
        if($pointSale) {
            $this->validate($request, [
                'reject_reason' => 'required',
            ]);
            
            $pointSale->status = Status::POINT_SALE_REJECTED;
            $pointSale->rejected_date = date('Y:m:d H:i:s');
            $pointSale->reject_reason = $request->input('reject_reason');
            $pointSale->save();

            return response()->json($pointSale);
        }
        else {
            return response(['error' => 'Not found data for ID '. $id], 404);
        }
    }
}