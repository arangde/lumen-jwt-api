<?php

namespace App\Http\Controllers;

use Validator;
use App\Point;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class PointController extends BaseController 
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
        $points = Point::with('member')->get();
        return response()->json($points);
    }
}