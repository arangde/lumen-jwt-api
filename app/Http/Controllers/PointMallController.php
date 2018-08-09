<?php

namespace App\Http\Controllers;

use Validator;
use App\PointMall;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use GenTux\Jwt\GetsJwtToken;

class PointMallController extends BaseController 
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
        $pointMalls = PointMall::with('member')->get();
        return response()->json($pointMalls);
    }
    
    public function create(Request $request) {
        $this->validate($request, [
            'member_id' => 'required',
            'item_name' => 'required',
            'item_point' => 'required',
        ]);

        $pointMall = PointMall::create($request->all());
        $pointMall->load('member');

        return response()->json($pointMall, 201);
    }

    public function get($id) {
        $pointMall = PointMall::with('member')->find($id);
        if($pointMall) {
            $payload = $this->jwtPayload();
            if(isset($payload['context']['permission']) && $payload['context']['permission'] === 'member') {
                if($payload['context']['id'] === $pointMall->member_id) {
                    return response($pointMall);
                } else {
                    return response(['error' => 'You have not permission.'], 401);
                }
            } else {
                return response($pointMall);
            }
        }
        else {
            return response(['error' => 'Not found data for ID '. $id], 404);
        }
    }

    public function update(Request $request, $id) {
        $pointMall = PointMall::find($id);
        if($pointMall) {
            $this->validate($request, [
                'item_name' => 'required',
                'item_point' => 'required',
            ]);
            
            $pointMall->product_name = $request->input('item_name');
            $pointMall->product_price = $request->input('item_point');
            $pointMall->save();
            $pointMall->load('member');

            return response()->json($pointMall);
        }
        else {
            return response(['error' => 'Not found data for ID '. $id], 404);
        }
    }

    public function delete($id) {
        $pointMall = PointMall::find($id);
        if($pointMall) {
            $pointMall->delete();
            return response('Deleted Successfully');
        }
        else {
            return response(['error' => 'Not found data for ID '. $id], 404);
        }
    }
}