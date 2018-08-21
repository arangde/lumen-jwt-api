<?php

namespace App\Http\Controllers;

use Validator;
use App\User;
use Illuminate\Http\Request;
use GenTux\Jwt\JwtToken;
use GenTux\Jwt\GetsJwtToken;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Routing\Controller as BaseController;

class AdminController extends BaseController 
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
     * Authenticate a user and return the token if the provided credentials are correct.
     * 
     * @param  \App\User   $user 
     * @return mixed
     */
    public function authenticate(User $user, JwtToken $jwt) {
        $this->validate($this->request, [
            'email'     => 'required|email',
            'password'  => 'required'
        ]);

        // Find the user by email
        $user = User::where('email', $this->request->input('email'))->first();

        if (!$user) {
            return response()->json([
                'error' => __('Email does not exist.')
            ], 400);
        }

        // Verify the password and generate the token
        if (Hash::check($this->request->input('password'), $user->password)) {
            return response()->json([
                'token' => $jwt->setSecret(env('JWT_SECRET'))->createToken($user)
            ], 200);
        }

        // Bad Request response
        return response()->json([
            'error' => __('Email or password is wrong.')
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

            if(isset($payload['context']['permission']) && $payload['context']['permission'] === 'admin') {
                $payload['exp'] = time() + 7200;

                return response()->json([
                    'token' => $jwt->setSecret(env('JWT_SECRET'))->createToken($payload)
                ], 200);
            }
        }
        
        return response()->json([
            'error' => 'Unauthorized.'
        ], 401);
    }

    /**
     * Get dashboard total info
     */
    public function getDashboard() {
        $members = \App\Member::all();
        $lastIncomes = \App\Income::with('member')->orderBy('created_at', 'desc')->limit(10)->get();
        $requestedWithdrawals = \App\Withdrawal::with('member')
            ->where('status', '=', \App\Status::WITHDRAWAL_REQUESTED)->get();
        // $lastSales = $sales->sortByDesc('created_at')->values();
        // $lastSales->splice(10);

        return response()->json([
            'totalMembers' => $members->count(),
            'totalIncomes' => $members->sum('balance'),
            'totalPoints' => $members->sum('point'),
            // 'totalSales' => $sales->count(),
            'lastIncomes' => $lastIncomes,
            'requestedWithdrawals' => $requestedWithdrawals
        ], 200);
    }
}