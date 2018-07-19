<?php

namespace App\Http\Controllers;

use Validator;
use App\Member;
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

    public function getMembers() {
        $members = Member::all();
        return response()->json($members);
    }

    public function getProfile() {
        $token = $this->jwtToken();
        $email = $token->payload('context.email');
  
        $member = Member::with('refers', 'incomes', 'pointsList', 'withdrawals', 'sales')->where("email", "=", $email)->first();
        if ($member) {
          return response()->json($member);
        } else {
          return response(['error' => 'Member not found'], 404);
        }
    }
}