<?php

namespace App\Http\Controllers;
use App\Serialisers\CustomSerialiser;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReconciledDeductionsExport;
use Importer;
use Exporter;

use Illuminate\Http\Request;
use App\Center;
use App\Member;
use App\LedgerSnapShot;
use App\Exports\LedgerSnapShot as LedgerSnapShotExport;
use Carbon\Carbon;

class LedgerSnapShotController extends Controller
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
     * Show page where ledger snap shot can be initiated
     */
    function ledgerSnapShot() {

        $data['paypoints'] = Center::pluck('name', 'id');
        $data['snapShots'] = LedgerSnapShot::orderBy('created_at')->get();
        // dd($data['snapShots']);

        return view('ledger.snapshot', $data);
    }


    function ledgerSnapShotPost(Request $request) {
        // dd($request->all());
        $deduction_for = $request->deduction_for;
        $pay_point_id = $request->pay_point_id;
        
        $export = new LedgerSnapShot;
        $export->deduction_for = $deduction_for ? $deduction_for : Carbon::now();
        $export->pay_point_id = $pay_point_id;
        $export->done_by = auth()->user()->ippis;
        $export->save();

        flash('Snapshot is being created in the background')->success();
        return redirect('ledger/snap-shot');
    }

    /**
     * Download snapshot as excel file
     */
    function downloadSnapshotFile($id, $center) {

        return Excel::download(new LedgerSnapShotExport($id), 'Snapshot_'.$center.'_'.Carbon::now().'.xlsx');

    }


    /**
     * function performed via cron jon to generate snap shot for selected center
     */
    function generateLedgerSnapShot(Request $request) {
        
        $snapShot = LedgerSnapShot::where('is_done', 0)->first();

        if($snapShot->isEmpty()) {
            return;
        }

        $exports = []; // variable to hold entier export data

        if ($snapShot) {
            $pay_point_id = $snapShot->pay_point_id;

            $members = Member::where('pay_point', $pay_point_id)->get();

            foreach($members as $member) {

                $ippis               = $member->ippis;
                $surname             = $member->lname;
                $othernames          = $member->fname;
                $shares              = $member->sharesBalance();
                $monthlyContribution = $member->latest_monthly_saving() ? $member->latest_monthly_saving()->amount : 0;
                $totalSavings        = $member->savingsBalance();
                $currentLTLAmount    = $member->latest_long_term_loan() ? $member->latest_long_term_loan()->total_amount : 0;
                $currentLTLDuration  = $member->latest_long_term_loan() ? $member->latest_long_term_loan()->no_of_months : 0;
                $currentLTLBalance   = $member->longTermLoanBalance();
                $currentSTLAmount    = $member->latest_short_term_loan() ? $member->latest_short_term_loan()->total_amount : 0;
                $currentSTLDuration  = $member->latest_short_term_loan() ? $member->latest_short_term_loan()->no_of_months : 0;
                $currentSTLBalance   = $member->shortTermLoanBalance();
                $currentCOMLAmount   = $member->latest_commodity_loan() ? $member->latest_commodity_loan()->total_amount : 0;
                $currentCOMLDuration = $member->latest_commodity_loan() ? $member->latest_commodity_loan()->no_of_months : 0;
                $currentCOMLBalance  = $member->commodityLoanBalance();
                
                $exports[] = [
                    $ippis, 
                    $surname,
                    $othernames, 
                    $shares, 
                    $monthlyContribution, 
                    $totalSavings, 
                    $currentLTLAmount, 
                    $currentLTLDuration,
                    $currentLTLBalance, 
                    $currentSTLAmount, 
                    $currentSTLDuration, 
                    $currentSTLBalance,
                    $currentCOMLAmount, 
                    $currentCOMLDuration, 
                    $currentCOMLBalance
                ];
                
            }
            
            // dd($export);
            $snapShot->exports = $exports;
            $snapShot->is_done = 1;
            $snapShot->save();
        }

        return 1;
    }
}
