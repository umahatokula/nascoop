<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Share;
use App\Center;
use App\ShareSetting;
use App\Member;
use App\MonthlySavingsPayment;
use App\Ledger;
use Carbon\Carbon;
use App\ActivityLog;
use App\Ledger_Internal;

class SharesController extends Controller
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
     *   Show shares bought
     **/
    function sharesBought () {
        // dd(request('pay_point_id'));

        $dateFrom = Carbon::today()->startOfMonth();
        $dateTo = Carbon::today()->endOfMonth();
        $pay_point_id = 9;

        $query = Share::query();

        if (request('dateFrom')) {
            $dateFrom = request('dateFrom');
            $dateTo= request('dateTo');
        }

        if (request('pay_point_id')) {
            $pay_point_id = request('pay_point_id');
        }

        // get records that belongs to members from chosen paypoint
        $query = $query->whereHas('member', function ($q) use($pay_point_id) {
            $q->where('pay_point', $pay_point_id);
        });


        // get records in date range
        if ($dateFrom == $dateTo) {            
            $query = $query->where('date_bought', $dateFrom);
        } else {
            $query = $query->whereBetween('date_bought', [$dateFrom, $dateTo]);
        }

        // dd($query);

        $data['sharesBought'] = $query->paginate(50);
        $data['dateFrom'] = $dateFrom;
        $data['dateTo'] = $dateTo;
        $data['pay_point_id'] = $pay_point_id;
        $data['paypoints'] = Center::pluck('name', 'id');

        return view('shares.bought', $data);
    }


    /**
     *   Show shares liquidated
     **/
    function sharesLiquidated () {
        $data['sharesLiquidated'] = [];

        return view('shares.liquidated', $data);
    }


    /**
     *   Show memebrs shares
     **/
    public function sharesShow($ippis) {
        
        $data['member'] = Member::where('ippis', $ippis)->first();
        $data['shares'] = Share::where('ippis', $ippis)->get();

        return view('shares.shares', $data);
    }


    /**
     *   Show form for shares purchase
     **/
    public function sharesBuy($ippis) {
        
        $member = Member::where('ippis', $ippis)->first();
        $settings = ShareSetting::first(); 

        // ensure certain bio details are entered
        $ensureMemberDetails = $member->ensureMemberDetails();
        if (!$ensureMemberDetails):            
            flash('To proceed, you must update the following details: Phone number, Email, Paypoint and Centre')->error();
            // return redirect()->route('editMember', $ippis);
        endif;
        
        if (!$settings) {
            flash('Please setup shares settings first')->warning();
            return redirect()->back();
        }

        $payment_methods = [
            ['key' => 'savings',            'value' => 'Savings'],
            ['key' => 'bank_deposit',       'value' => 'Bank Deposit'],
            ['key' => 'salary',             'value' => 'Salary'],
        ];

        $lastLongTermPayment = $member->latest_long_term_payment();
        $lastMonthlySaving = $member->latest_monthly_savings_payment();

        if(request()->ajax()) {
            return response()->json([
                'member'                 => $member,
                'payment_methods'        => $payment_methods,
                'settings'               => $settings,
                'last_long_term_payment' => $lastLongTermPayment ? $lastLongTermPayment->load('longTermLoan') : 0,
                'last_monthly_saving'    => $lastMonthlySaving,
            ]);
        }

        $data['member'] = $member;
        $data['payment_methods'] = $payment_methods;

        return view('shares.buy', $data);
    }


    /**
     *   Post shares purchase to DB
     **/
    public function sharesBuyPost(Request $request) {
        // dd($request->all());

        $rules = [
            'ippis'            => 'required',
            'units'            => 'required',
            'amount'           => 'required',
            'rate_when_bought' => 'required',

        ];

        $messages = [
            'ippis.required'  => 'The ippis is required',
            'units.required'  => 'The units required',
            'amount.required' => 'The amount required',
        ];

        $this->validate($request, $rules, $messages);


        $ippis = $request->ippis;

        $member = Member::where('ippis', $ippis)->first();

        $lastMonthlySavingRecord = $member->latest_monthly_saving();

        if(isset($lastMonthlySavingRecord)) {
            $lastMonthlySavingsPaymentRecord = $member->latest_monthly_savings_payment();
            
            $savings_bal = isset($lastMonthlySavingsPaymentRecord) ? $lastMonthlySavingsPaymentRecord->bal - $request->amount : 0 - $request->amount;

        } else {

            Toastr::error('It seems there is no money in this account', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.savings', $ippis);

        }

        // generate a code to be used for this ledger entry
        $ledger = new Ledger;
        $trxnNumber = $ledger->generateTrxnNumber();

        
        if ($request->payment_method == 'savings') {

            // make entry in long term payments table
            $msPayment                      = new MonthlySavingsPayment;
            $msPayment->trxn_number         = $trxnNumber;
            $msPayment->trxn_type           = 'buy_shares';
            $msPayment->is_authorized       = 1;
            $msPayment->monthly_saving_id   = $lastMonthlySavingRecord->id;
            $msPayment->ippis               = $request->ippis;
            $msPayment->pay_point           = $member->pay_point;
            $msPayment->ref                 = 'Shares bought via savings';
            $msPayment->withdrawal_date     = $request->date_bought;
            $msPayment->dr                  = $request->amount;
            $msPayment->cr                  = 0.00;
            $msPayment->bal                 = $savings_bal;
            $msPayment->month               = Carbon::today()->format('m');
            $msPayment->year                = Carbon::today()->format('Y');
            $msPayment->is_withdrawal       = 3;
            $msPayment->done_by             = auth()->user()->ippis;
            $msPayment->save();


            // make ledger entry
            $ledger                  = new Ledger;
            $ledger->trxn_number     = $trxnNumber;
            $ledger->trxn_type       = 'buy_shares';
            $ledger->is_authorized   = 1;
            $ledger->member_id       = $member->id;
            $ledger->pay_point       = $member->pay_point;
            $ledger->date            = Carbon::today()->format('Y-m-d');
            $ledger->ref             = 'Shares bought via savings';
            $ledger->withdrawal_date = $request->date_bought;
            $ledger->savings_dr      = $request->amount;
            $ledger->savings_cr      = 0.00;
            $ledger->savings_bal     = $savings_bal;
            $ledger->long_term_dr    = 0.00;
            $ledger->long_term_cr    = 0.00;
            $ledger->long_term_bal   = $member->longTermLoanBalance();
            $ledger->short_term_dr   = 0.00;
            $ledger->short_term_cr   = 0.00;
            $ledger->short_term_bal  = $member->shortTermLoanBalance();
            $ledger->commodity_dr    = 0.00;
            $ledger->commodity_cr    = 0.00;
            $ledger->commodity_bal   = $member->commodityLoanBalance();
            $ledger->done_by         = auth()->user()->ippis;
            $ledger->save();

            $member->savings_locked = 0; // lock savings
            $member->save();

            $activityLog = new ActivityLog;
            $activityLog->logThis($trxnNumber, $member->ippis, '(Shares bought from savings) '.$member->ippis.' '.$member->full_name, $request->amount, 0, auth()->user()->ippis);
        }

        $share                   = new Share;
        $share->ippis            = $request->ippis;
        $share->date_bought      = $request->date_bought;
        $share->units            = $request->units;
        $share->amount           = $request->amount;
        $share->rate_when_bought = $request->rate_when_bought;
        $share->trxn_number      = $trxnNumber;
        $share->payment_method   = $request->payment_method;

        if ($request->payment_method == 'savings') {
            $share->is_authorized    = 1;
        }
        if ($request->payment_method == 'bank_deposit') {
            $share->is_authorized    = 0;
        }
        
        $share->save();

        if ($share->is_authorized == 1) {
            $activityLog = new ActivityLog;
            $activityLog->logThis($share->trxn_number , $share->ippis, 'SHARES BOUGHT', $share->amount, 1, auth()->user()->ippis);

        }

        if ($share->is_authorized == 0) {
            $activityLog = new ActivityLog;
            $activityLog->logThis($share->trxn_number , $share->ippis, 'SHARES BOUGHT', $share->amount, 0, auth()->user()->ippis);
        }

        return response()->json(['shares' => $share]);
    }


    /**
     * Authorize shares purchase by members
     **/
    public function authorizeSharesTransaction(Request $request) {
        // dd($request->all());

        $trxn_number = $request->trxn_number;
        $status      = $request->action == 'authorize' ? 1 : 2;
        $ippis       = $request->ippis;
        $bank        = $request->bank;

        if ($status == 1) {
            $member = Member::where('ippis', $ippis)->first();

            if ($member) {

                $share                 = Share::where('trxn_number', $trxn_number)->first();
                $share->is_authorized  = 1;
                
                $share->save();

                // update total shares on members table
                $member->shares_amount += $share->amount;
                $member->save();

                ActivityLog::where('trxn_number', $trxn_number)->update(['is_authorized' => 1]);

                // fire event to save share purchase in COA
                $ledgerInternal = new Ledger_Internal;
                $ledgerInternal->recordSharesBought($member, $share->amount, ' SHARES BOUGHT ['. $member->ippis. ']', $bank);
            }
            

        } else {

            Share::where('trxn_number', $trxn_number)->update(['is_authorized' => 2]);

        }

        return redirect()->back();
    }


    /**
     * Show form to liquadate shares
     **/
    public function liquidate($ippis) {

        $member = Member::where('ippis', $ippis)->first();
        
        if ($member->is_active) {
            flash('Member must be deactivated to liquidate shares')->warning();
            return redirect('shares');
        }

        $data['member'] = $member;

        return view('shares.liquidate', $data);
    }
}
