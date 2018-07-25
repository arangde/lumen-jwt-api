<?php

namespace App\Http\Controllers;

use Validator;
use App\Sale;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class SaleController extends BaseController 
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
        $sales = Sale::with('member')->get();
        return response()->json($sales);
    }
    
    public function create(Request $request) {
        $this->validate($request, [
            'member_id' => 'required',
            'product_name' => 'required',
        ]);

        $sale = Sale::create($request->all());
        $sale->load('member');

        return response()->json($sale, 201);
    }

    public function get($id) {
        $sale = Sale::find($id);
        if($sale) {
            return response($sale);
        }
        else {
            return response(['error' => 'Not found sale for ID '. $id], 404);
        }
    }

    public function update(Request $request, $id) {
        $sale = Sale::find($id);
        if($sale) {
            $this->validate($request, [
                'member_id' => 'required',
                'product_name' => 'required',
            ]);
            
            $sale = Sale::update($request->all());

            return response()->json($sale);
        }
        else {
            return response(['error' => 'Not found sale for ID '. $id], 404);
        }
    }

    public function delete($id) {
        $sale = Sale::find($id);
        if($sale) {
            $sale->delete();
            return response('Deleted Successfully');
        }
        else {
            return response(['error' => 'Not found sale for ID '. $id], 404);
        }
    }
}