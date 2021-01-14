<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\LoanDuration;

class LoanDurationController extends Controller
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
    public function loanDurations() {
        $data['settings'] = LoanDuration::all();

        return view('loansDurations.durations', $data);
    }

    public function loanDurationsPost() {

    }
}
