<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\LoanDuration;

class LoanSettingController extends Controller
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

    /**
     * Show loans settings page
     */
    public function loanSettings() {
        $durations = LoanDuration::all();

        if (request()->ajax()) {
            return response()->json([
                'durations' => $durations,
            ]);
        }

        $data['durations'] = LoanDuration::all();

        return view('loanSettings.settings', $data);
    }

    public function loanSettingsPost() {
        // dd(request()->all(), request('code'));

        LoanDuration::where('code', request('code'))->delete();

        if(request('code') == 'ltl') {
            $settings = request('ltl');
        }

        if(request('code') == 'stl') {
            $settings = request('stl');
        }

        if(request('code') == 'comm') {
            $settings = request('comm');
        }
    
        foreach($settings as $d) {
            // dd($d['code']);
            $loanDuration = new LoanDuration;
            $loanDuration->code               = request('code');
            $loanDuration->duration           = $d['duration'];
            $loanDuration->number_of_months   = $d['number_of_months'];
            $loanDuration->interest           = $d['interest'];
            $loanDuration->determinant_factor = isset($d['determinant_factor']) ? $d['determinant_factor'] : null ;
            $loanDuration->save();
        }

        return response()->json([
            'durations' => $loanDuration,
        ]);
        
    }
}
