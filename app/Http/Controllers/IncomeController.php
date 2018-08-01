<?php

namespace App\Http\Controllers;

use Validator;
use App\Income;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use GenTux\Jwt\GetsJwtToken;

class IncomeController extends BaseController 
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
        $incomes = Income::with('member')->get();
        return response()->json($incomes);
    }

    public function get($id) {
        $income = Income::with('member')->find($id);
        if($income) {
            $payload = $this->jwtPayload();
            if(isset($payload['context']['permission']) && $payload['context']['permission'] === 'member') {
                if($payload['context']['id'] === $income->member_id) {
                    return response($income);
                } else {
                    return response(['error' => 'You have not permission.'], 401);
                }
            } else {
                return response($income);
            }
        }
        else {
            return response(['error' => 'Not found income for ID '. $id], 404);
        }
    }
}