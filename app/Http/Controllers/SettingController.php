<?php

namespace App\Http\Controllers;

use Validator;
use App\Setting;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class SettingController extends BaseController 
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
        $settings = Setting::all();
        return response()->json($settings);
    }
    
    public function create(Request $request) {
        $this->validate($request, [
            'setting_field' => 'required',
            'value' => 'required',
        ]);

        $setting = new Setting;
        $setting->name = $request->input('name');
        $setting->setting_field = $request->input('setting_field');
        $setting->value = $request->input('value');
        $setting->save();

        return response()->json($setting, 201);
    }

    public function update(Request $request) {
        $settings = Setting::all();
        $data = $request->all();

        $settings->each(function($setting) use($data) {
            if (isset($data[$setting->setting_field])) {
                $setting->value = $data[$setting->setting_field];
                $setting->save();
            }
        });

        $settings = Setting::all();
        return response()->json($settings);
    }

    public function delete($id) {
        $setting = Setting::find($id);
        if($setting) {
            $setting->delete();
            return response('Deleted Successfully');
        }
        else {
            return response(['error' => 'Not found setting for ID '. $id], 404);
        }
    }
}