<?php

namespace App\Http\Controllers;

use Validator;
use App\Member;
use Illuminate\Http\Request;
use GenTux\Jwt\JwtToken;
use GenTux\Jwt\GetsJwtToken;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Routing\Controller as BaseController;

class AuthController extends BaseController 
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

    /**
     * Authenticate a member and return the token if the provided credentials are correct.
     * 
     * @param  \App\Member   $member 
     * @return mixed
     */
    public function authenticate(Member $member, JwtToken $jwt) {
        $this->validate($this->request, [
            'email'     => 'required|email',
            'password'  => 'required'
        ]);

        // Find the member by email
        $member = Member::where('email', $this->request->input('email'))->first();

        if (!$member) {
            return response()->json([
                'error' => 'Email does not exist.'
            ], 400);
        }

        // Verify the password and generate the token
        if (Hash::check($this->request->input('password'), $member->password)) {
            return response()->json([
                'token' => $jwt->setSecret(env('JWT_SECRET'))->createToken($member)
            ], 200);
        }

        // Bad Request response
        return response()->json([
            'error' => 'Email or password is wrong.'
        ], 400);
    }

    /**
     * Authorize a token and response refreshed token
     */
    public function checkToken(JwtToken $jwt)
    {
        $token = $this->jwtToken();

        if ($token->validate()) {
            $payload = $token->payload();
            $payload['exp'] = time() + 7200;

            return response()->json([
                'token' => $jwt->setSecret(env('JWT_SECRET'))->createToken($payload)
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthorized.'
            ], 401);
        }
    }
}