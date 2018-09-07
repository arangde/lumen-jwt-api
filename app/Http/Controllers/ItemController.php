<?php

namespace App\Http\Controllers;

use Validator;
use App\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

        $item = new Item;
        $item->item_name = $request->input('item_name');
        $item->item_point = $request->input('item_point');
        $item->note = $request->input('note');

        if ($request->file('photo')) {
            $photo = Str::random(32);
            $destinationPath = env('IMAGES_PATH');
            $request->file('photo')->move($destinationPath, $photo);
            $item->photo_path = $destinationPath. $photo;
            $item->photo_url = env('APP_HOST'). $item->photo_path;
        }

        $item->save();

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

            if ($request->file('photo')) {
                $destinationPath = env('IMAGES_PATH');
                if (file_exists($item->photo_path)) {
                    unlink($item->photo_path);
                }

                $photo = Str::random(32);
                $request->file('photo')->move($destinationPath, $photo);
                $item->photo_path = $destinationPath. $photo;
                $item->photo_url = config('APP_HOST'). $item->photo_path;
            }
    
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