<?php

namespace App\Http\Controllers;

use Validator;
use App\User;
use App\Role;
use Illuminate\Http\Request;
use GenTux\Jwt\JwtToken;
use GenTux\Jwt\GetsJwtToken;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Routing\Controller as BaseController;

class UserController extends BaseController 
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
        $users = User::all();
        return response()->json($users);
    }

    public function create(Request $request) {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);

        $role = $request->input('role') ? $request->input('role') : Role::READ_ONLY;
        $user = new User;
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->role = $role;
        $user->password = app('hash')->make($request->input('password'));
        $user->save();
        
        return response($user, 201);
    }

    public function get($id) {
        $user = User::find($id);
        if($user) {
            return response($user);
        }
        else {
            return response(['error' => 'Not found user for ID '. $id], 404);
        }
    }

    public function update(Request $request, $id) {
        $user = User::find($id);
        if($user) {
            $this->validate($request, [
                'name' => 'required',
                'email' => 'required'
            ]);
            
            $user2 = User::where("email", "=", $request->input('email'))->where("id", "!=", $id)->first();
            if($user2) {
                return response(['error' => 'User email has already been registered.'], 422);
            }
            else {
                $role = $request->input('role') ? $request->input('role') : Role::READ_ONLY;
                $user->name = $request->input('name');
                $user->email = $request->input('email');
                $user->role = $role;
                if($request->input('password')) {
                    $user->password = app('hash')->make($request->input('password'));
                }
                $user->save();

                return response($user);
            }
        }
        else {
            return response(['error' => 'Not found user for ID '. $id], 404);
        }
    }

    public function delete($id) {
        $user = User::find($id);
        if($user) {
            $user->delete();
            return response('Deleted Successfully');
        }
        else {
            return response(['error' => 'Not found user for ID '. $id], 404);
        }
    }
}