<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ShareSetting;
use Toastr;

class ShareSettingController extends Controller
{  
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function sharesSettings() {
        $data['settings'] = ShareSetting::first();
        // dd($data['settings']);

        return view('settings.sharesSettings', $data);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sharesSettingsPost(Request $request)
    {
        // dd($request->all());

        $rules = [
            'rate' => 'required',
            'open_date' => 'required',
            'close_date' => 'required',

        ];

        $messages = [
            'rate.required' => 'The rate is required',
            'open_date.required' => 'The open date required',
            'close_date.required' => 'The close date required',
        ];

        $this->validate($request, $rules, $messages);

        $settings             = ShareSetting::first();

        if(!$settings) {
            $settings = new ShareSetting;
        }

        $settings->rate       = $request->rate;
        $settings->open_date  = $request->open_date;
        $settings->close_date = $request->close_date;
        $settings->save();

        Toastr::success('Settings Set', 'Success', ["positionClass" => "toast-bottom-right"]);

        return redirect()->back();
    }
}
