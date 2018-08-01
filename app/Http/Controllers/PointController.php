<?php

namespace App\Http\Controllers;

use Validator;
use App\Point;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use GenTux\Jwt\GetsJwtToken;

class PointController extends BaseController 
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
        $points = Point::with('member')->get();
        return response()->json($points);
    }
    
    public function get($id) {
        $point = Point::with('member')->find($id);
        if($point) {
            $payload = $this->jwtPayload();
            if(isset($payload['context']['permission']) && $payload['context']['permission'] === 'member') {
                if($payload['context']['id'] === $point->member_id) {
                    return response($point);
                } else {
                    return response(['error' => 'You have not permission.'], 401);
                }
            } else {
                return response($point);
            }
        }
        else {
            return response(['error' => 'Not found point for ID '. $id], 404);
        }
    }
}