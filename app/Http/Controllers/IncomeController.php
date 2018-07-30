<?php

namespace App\Http\Controllers;

use Validator;
use App\Income;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class IncomeController extends BaseController 
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
        $incomes = Income::with('member')->get();
        return response()->json($incomes);
    }
}