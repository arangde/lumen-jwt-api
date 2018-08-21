<?php

namespace App\Http\Controllers;

use Validator;
use App\Sale;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use GenTux\Jwt\GetsJwtToken;

class SaleController extends BaseController 
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
        $sales = Sale::with('member')->get();
        return response()->json($sales);
    }
    
    public function create(Request $request) {
        $this->validate($request, [
            'member_id' => 'required',
            // 'product_name' => 'required',
        ]);

        $sale = Sale::create($request->all());
        $sale->load('member');

        return response()->json($sale, 201);
    }

    public function get($id) {
        $sale = Sale::with('member')->find($id);
        if($sale) {
            $payload = $this->jwtPayload();
            if(isset($payload['context']['permission']) && $payload['context']['permission'] === 'member') {
                if($payload['context']['id'] === $sale->member_id) {
                    return response($sale);
                } else {
                    return response(['error' => __('You have not permission.')], 401);
                }
            } else {
                return response($sale);
            }
        }
        else {
            return response(['error' => __('Not found data for #:ID', ['ID' => $id])], 404);
        }
    }

    public function update(Request $request, $id) {
        $sale = Sale::find($id);
        if($sale) {
            $this->validate($request, [
                'product_price' => 'required',
            ]);
            
            // $sale->product_name = $request->input('product_name');
            $sale->product_price = $request->input('product_price');
            $sale->save();
            $sale->load('member');

            return response()->json($sale);
        }
        else {
            return response(['error' => __('Not found data for #:ID', ['ID' => $id])], 404);
        }
    }

    public function delete($id) {
        $sale = Sale::find($id);
        if($sale) {
            $sale->delete();
            return response('Deleted Successfully');
        }
        else {
            return response(['error' => __('Not found data for #:ID', ['ID' => $id])], 404);
        }
    }
}