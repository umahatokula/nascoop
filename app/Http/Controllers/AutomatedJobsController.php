<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\MonthlySaving;

class AutomatedJobsController extends Controller
{
    public function revertMonthlySavings() {
        $toBeReverted = MonthlySaving::where(['is_indefinite' => 0, 'revert_date' => Carbon::today()])->get();

        foreach ($toBeReverted as $s) {
            
            $monthlySaving                  = new MonthlySaving;
            $monthlySaving->ippis           = $s->ippis;
            $monthlySaving->amount          = $s->old_amount;
            $monthlySaving->old_amount      = $s->old_amount;
            $monthlySaving->new_amount      = $s->new_amount;
            $monthlySaving->is_indefinite   = 1;
            $monthlySaving->revert_date     = $s->revert_date;
            // $monthlySaving->done_by         = auth()->user()->ippis;
            $monthlySaving->save();
        }
        
    }
}
