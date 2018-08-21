<?php

namespace App\Http\Controllers;

use Validator;
use App\Item;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use GenTux\Jwt\GetsJwtToken;

class ItemController extends BaseController 
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
        $items = Item::all();
        return response()->json($items);
    }
    
    public function create(Request $request) {
        $this->validate($request, [
            'item_name' => 'required',
            'item_point' => 'required',
        ]);

        $item = Item::create($request->all());

        return response()->json($item, 201);
    }

    public function get($id) {
        $item = Item::find($id);
        if($item) {
            return response($item);
        }
        else {
            return response(['error' => __('Not found data for #:ID', ['ID' => $id])], 404);
        }
    }

    public function update(Request $request, $id) {
        $item = Item::find($id);
        if($item) {
            $this->validate($request, [
                'item_name' => 'required',
                'item_point' => 'required',
            ]);
            
            $item->item_name = $request->input('item_name');
            $item->item_point = $request->input('item_point');
            $item->note = $request->input('note');
            $item->save();

            return response()->json($item);
        }
        else {
            return response(['error' => __('Not found data for #:ID', ['ID' => $id])], 404);
        }
    }

    public function delete($id) {
        $item = Item::find($id);
        if($item) {
            $item->delete();
            return response('Deleted Successfully');
        }
        else {
            return response(['error' => __('Not found data for #:ID', ['ID' => $id])], 404);
        }
    }
}