<?php

namespace App\Http\Controllers;

use App\Member;
use App\Commodity;
use App\CommodityPayment;
use App\LongTermPayment;
use App\MonthlySavingsPayment;
use App\Ledger;
use App\ActivityLog;
use App\LoanDuration;
use Carbon\Carbon;
use Toastr;
use App\Events\TransactionOccured;
use App\TransactionType_Ext;
use App\Ledger_Internal;

use Illuminate\Http\Request;

class CommodityController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Commodity loans
     */
    function commodity(Request $request, $ippis) {

        $date_from = Carbon::now()->startOfYear();
        $date_to = Carbon::now()->endOfYear();
        $data['member'] = Member::where('ippis', $ippis)->first();

        if(!isset($data['member'])) {
            Toastr::error('This member does not exist', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.longTermLoans', $ippis);
        }

        $data['commodityLoans'] = Commodity::with('payments')->where('ippis', $ippis)->get();
        // dd($data['longTermLoans']);

        $commodityLoans = Commodity::query();
        $commodityLoans = $commodityLoans->with('payments')->where('ippis', $ippis);

        if ($request->date_from) {    
            $date_from = $request->date_from;
            $date_to = $request->date_to; 
        }
            
        $commodityLoans = $commodityLoans->whereBetween('loan_date', [$date_from, $date_to]);

        $data['date_from'] = $date_from;
        $data['date_to'] = $date_to;
        $data['commodityLoans'] = $commodityLoans->get();

        return view('members.commodity.commodity', $data);
    }

    /**
     * commodity loans
     */
    function newCommodityLoan($ippis) {

        $member = Member::where('ippis', $ippis)->first();
        
        if ($member->is_active == 0) {
            Toastr::error('This member has been deactivated', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.dashboard', $member->ippis);
        }

        // ensure certain bio details are entered
        $ensureMemberDetails = $member->ensureMemberDetails();
        if (!$ensureMemberDetails):            
            flash('TO proceed, you must update the following details: Phone number, Email, Paypoint and Centre')->error();
            return redirect()->route('editMember', $ippis);
        endif;

        if(!isset($member)) {
            Toastr::error('This member does not exist', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.longTermLoans', $ippis);
        }

        $commodityLoans = Commodity::pluck('ref', 'id');


        // get latest long term loan payment
        $lastLongTermPayment = $member->latest_long_term_payment();

        // get monthly savings balance
        $last_savings = $member->latest_monthly_savings_payment();
        $savings_bal = isset($last_savings) ? $last_savings->bal : 0;

        // get latest commodity loan
        $lastCommodityLoan = $member->latest_commodities_payment();

        $periods = [
            ['key' => '1',  'value' => "1 Months",],
            ['key' => '2',  'value' => "2 Months",],
            ['key' => '3',  'value' => "3 Months",],
            ['key' => '4',  'value' => "4 Months",],
            ['key' => '5',  'value' => "5 Months",],
            ['key' => '6',  'value' => "6 Months",],
        ];
        $periods = LoanDuration::where('code', 'comm')->get();
        
        if(request()->ajax()) {

            return [
                'savings'                   => $last_savings, 
                'periods'                   => $periods,
                'last_long_term_payment'    => $lastLongTermPayment,
                'savings_bal'               => $savings_bal,
                'last_commodity_loan'       => $lastCommodityLoan,
            ];
        }

        $data['commodityLoans'] = $commodityLoans;
        $data['member'] = $member;   

        return view('members.commodity.new_commodity_loan', $data);
    }

    /**
     * Post commodity loans
     */
    function postNewCommodityLoan(Request $request, $ippis) {
        // dd($request->all(), $ippis);

        $rules = [
            'ref' => 'required',
            'loan_date' => 'required',
            'no_of_months' => 'required',
            'ippis' => 'required',
            'total_amount' => 'numeric|required',
            // 'email' => 'email|unique:member,email',

        ];

        $messages = [
            'ref.required' => 'The description is required',
            'loan_date.required' => 'The loan date is required',
            'no_of_months.required' => 'The number of months is required',
            'ippis.required' => 'This IPPIS Number is required',
            'total_amount.required' => 'Kindly enter the loan amount',
        ];

        $this->validate($request, $rules, $messages);

        $member = Member::where('ippis', $ippis)->first();

        $lastCommodityRecord = $member->latest_commodity_loan();

        if(isset($lastCommodityRecord)) {
            
            $lastCommodityPaymentRecord = $member->latest_commodities_payment();
            
            $commodity_bal = isset($lastCommodityPaymentRecord) ? $lastCommodityPaymentRecord->bal + $request->total_amount : $request->total_amount;

        } else {

            $commodity_bal = $request->total_amount;

        }

        // generate a code to be used for this ledger entry
        $ledger = new Ledger;
        $trxnNumber = $ledger->generateTrxnNumber();

        // get loan end date
        $loanEndDate = Carbon::parse($request->loan_date)->addMonths($request->no_of_months);

        // last loan
        $lastCommodityLoanBal = $member->latest_commodities_payment() ? $member->latest_commodities_payment()->bal : 0;

        // make entry in commodity loan table
        $commodity                 = new Commodity;
        $commodity->pay_point      = $member->pay_point;
        $commodity->ref            = $request->ref;
        $commodity->loan_date      = $request->loan_date;
        $commodity->loan_end_date  = $loanEndDate;
        $commodity->ippis          = $ippis;
        $commodity->no_of_months   = $request->no_of_months;
        $commodity->total_amount   = $request->total_amount;
        $commodity->monthly_amount = ($request->total_amount + $lastCommodityLoanBal) / $request->no_of_months;
        $commodity->done_by        = auth()->user()->ippis;
        $commodity->save();

        // make entry in commodity payments table
        $commodityPayment = new CommodityPayment;
        $commodityPayment->trxn_number     = $trxnNumber;
        $commodityPayment->trxn_type     = 'coml';
        $commodityPayment->is_authorized  = 0;
        $commodityPayment->ippis = $ippis;
        $commodityPayment->pay_point      = $member->pay_point;
        $commodityPayment->ref = $request->ref;
        $commodityPayment->loan_date = $request->loan_date;
        $commodityPayment->commodity_id = $commodity->id;
        $commodityPayment->dr = $request->total_amount;
        $commodityPayment->cr = 0.00;
        $commodityPayment->bal = $commodity_bal;
        $commodityPayment->month = Carbon::today()->format('m');
        $commodityPayment->year = Carbon::today()->format('Y');
        $commodityPayment->done_by = auth()->user()->ippis;
        $commodityPayment->save();


        // make ledger entry
        $ledger                 = new Ledger;
        $ledger->trxn_number    = $trxnNumber;
        $ledger->trxn_type      = 'coml';
        $ledger->is_authorized  = 0;
        $ledger->member_id      = $member->id;
        $ledger->pay_point      = $member->pay_point;
        $ledger->date           = $request->loan_date;
        $ledger->ref            = $request->ref;
        $ledger->loan_date      = $request->loan_date;
        $ledger->savings_dr     = 0.00;
        $ledger->savings_cr     = 0.00;
        $ledger->savings_bal    = $member->savingsBalance();
        $ledger->long_term_dr   = 0.00;
        $ledger->long_term_cr   = 0.00;
        $ledger->long_term_bal  = $member->longTermLoanBalance();
        $ledger->short_term_dr  = 0.00;
        $ledger->short_term_cr  = 0.00;
        $ledger->short_term_bal = $member->shortTermLoanBalance();
        $ledger->commodity_dr   = $request->total_amount;
        $ledger->commodity_cr   = 0.00;
        $ledger->commodity_bal  = $commodity_bal;
        $ledger->done_by        = auth()->user()->ippis;
        $ledger->save();

        $activityLog = new ActivityLog;
        $activityLog->logThis($trxnNumber, $member->ippis, '(Comm LOAN)'.$request->ref, $request->total_amount, 0, auth()->user()->ippis);

        // dd($commodity, $ledger);

        Toastr::success('Loan successful', 'Success', ["positionClass" => "toast-bottom-right"]);
        return redirect()->route('members.longTermLoans', $ippis);
    }

    /**
     * Show reayment form
     */
    function commodityLoanRepayment($ippis) {
        
        $member = Member::where('ippis', $ippis)->first();
        
        if ($member->is_active == 0) {
            Toastr::error('This member has been deactivated', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.dashboard', $member->ippis);
        }

        // ensure certain bio details are entered
        $ensureMemberDetails = $member->ensureMemberDetails();
        if (!$ensureMemberDetails):            
            flash('TO proceed, you must update the following details: Phone number, Email, Paypoint and Centre')->error();
            return redirect()->route('editMember', $ippis);
        endif;

        if(!isset($member)) {
            Toastr::error('This member does not exist', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.longTermLoans', $ippis);
        }
        $commodityLoans = Commodity::pluck('ref', 'id');

        $lastLongTermPayment = $member->latest_long_term_payment();

        $last_commodity_loan_payment = $member->latest_commodities_payment() ? : 0;

        $last_savings = $member->latest_monthly_savings_payment();
        $savings_bal = isset($last_savings) ? $last_savings->bal : 0;

        $repaymentModes = [
            ['key' => 'savings', 'value' => 'From savings'],
            ['key' => 'direct_deduction', 'value' => 'Direct deduction'],
            ['key' => 'bank_deposit', 'value' => 'Bank deposit'],
        ];

        if (request()->ajax()) {
            return [
                'commodity_loans'             => $commodityLoans,
                'last_commodity_loan_payment' => $last_commodity_loan_payment,
                'member'                      => $member,
                'repayment_modes'             => $repaymentModes,
                'last_long_term_payment'      => $lastLongTermPayment,
                'savings_bal'                 => $savings_bal,
            ];
        }

        $data['commodityLoans'] = $commodityLoans;
        $data['member']         = $member;
        $data['repaymentModes'] = $repaymentModes;
        $data['savings_bal']    = $savings_bal;

        return view('members.commodity.repayment', $data);
    }

    /**
     * Save reayment form
     */
    function postCommodityLoanRepayment(Request $request, $ippis) {
        // dd($request->all(), $ippis);

        $rules = [
            'ref' => 'required',
            'deposit_date' => 'required',
            'ippis' => 'required',
            'total_amount' => 'numeric|required',
            'repayment_mode' => 'required',
            // 'email' => 'email|unique:member,email',

        ];

        $messages = [
            'ref.required' => 'The description is required',
            'deposit_date.required' => 'The repayment date is required',
            'ippis.required' => 'This IPPIS Number is required',
            'total_amount.required' => 'Kindly enter the loan amount',
            'repayment_mode.required' => 'Kindly select a repayment type',
        ];

        $this->validate($request, $rules, $messages);

        $member = Member::where('ippis', $ippis)->first();

        $CommodityRecord = $member->latest_commodity_loan();

        if(isset($CommodityRecord)) {
            // $CommodityPaymentRecord = CommodityPayment::where('commodity_id', $CommodityRecord->id)->latest('id')->first();
            $CommodityPaymentRecord = $member->latest_commodities_payment();
            
            $commodity_bal = isset($CommodityPaymentRecord) ? $CommodityPaymentRecord->bal - $request->total_amount : 0 - $request->total_amount;

        } else {

            Toastr::error('No Commodity Loan exists', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.commodity', $ippis);

        }

        // generate a code to be used for this ledger entry
        $ledger4trxnNumber = new Ledger;
        $trxnNumber = $ledger4trxnNumber->generateTrxnNumber();
        
        if($request->repayment_mode == 'savings') {

            $lastMonthlySavingRecord = $member->latest_monthly_saving();

            $lastMonthlySavingsPaymentRecord = $member->latest_monthly_savings_payment();
                
            $savings_bal = isset($lastMonthlySavingsPaymentRecord) ? $lastMonthlySavingsPaymentRecord->bal - $request->total_amount : 0 - $request->total_amount;


            // make entry in long term payments table
            $msPayment                    = new MonthlySavingsPayment;
            $msPayment->monthly_saving_id = $lastMonthlySavingRecord ? $lastMonthlySavingRecord->id : 0;
            $msPayment->trxn_number       = $trxnNumber;
            $msPayment->trxn_type         = 'coml_Rp_Savings';
            $msPayment->is_authorized     = 1;
            $msPayment->ippis             = $request->ippis;
            $msPayment->pay_point         = $member->pay_point;
            $msPayment->ref               = $request->ref;
            $msPayment->withdrawal_date   = $request->deposit_date;
            $msPayment->dr                = $request->total_amount;
            $msPayment->cr                = 0.00;
            $msPayment->bal               = $savings_bal;
            $msPayment->month             = Carbon::today()->format('m');
            $msPayment->year              = Carbon::today()->format('Y');
            $msPayment->done_by           = auth()->user()->ippis;
            $msPayment->save();

            // make entry in short term payments table
            // $commodityPayment                = new CommodityPayment;
            // $commodityPayment->trxn_number   = $trxnNumber;
            // $commodityPayment->trxn_type     = 'coml_Rp_Savings';
            // $commodityPayment->is_authorized = $request->repayment_mode == 'savings' ? 1 : 0;
            // $commodityPayment->ippis         = $ippis;
            // $commodityPayment->pay_point     = $member->pay_point;
            // $commodityPayment->ref           = $request->ref;
            // $commodityPayment->deposit_date  = $request->deposit_date;
            // $commodityPayment->commodity_id  = $CommodityRecord->id;
            // $commodityPayment->dr            = 0.00;
            // $commodityPayment->cr            = $request->total_amount;
            // $commodityPayment->bal           = $commodity_bal;
            // $commodityPayment->month         = Carbon::today()->format('m');
            // $commodityPayment->year          = Carbon::today()->format('Y');
            // $commodityPayment->done_by       = auth()->user()->ippis;
            // $commodityPayment->save();

            // make ledger entry
            $ledger                = new Ledger;
            $ledger->trxn_number   = $trxnNumber;
            $ledger->trxn_type     = 'coml_Rp_Savings';
            $ledger->is_authorized = 1;
            $ledger->member_id     = $member->id;
            $ledger->pay_point     = $member->pay_point;
            $ledger->date          = Carbon::today()->format('Y-m-d');
            $ledger->ref           = $request->ref;
            $ledger->deposit_date  = $request->deposit_date;
            $ledger->savings_dr    = $request->total_amount;
            $ledger->savings_cr    = 0.00;
            $ledger->savings_bal   = $savings_bal;
            $ledger->done_by       = auth()->user()->ippis;
            $ledger->save();

            // fire event to save trxn in ledger transactions
            $ledgerInternal = new Ledger_Internal;
            $ledgerInternal->recordCOMLRepaymentViaSavings($member, $msPayment->dr, $member->ippis.' COML Repay. Sav.');

            $activityLog = new ActivityLog;
            $activityLog->logThis($trxnNumber, $member->ippis, '(Comm Loan Repayment from Savings) '.$request->ref, $request->total_amount, 1, auth()->user()->ippis);
        }

        // make entry in short term payments table
        $commodityPayment                = new CommodityPayment;
        $commodityPayment->trxn_number   = $trxnNumber;
        $commodityPayment->trxn_type     = 'coml_Rp_Deposit';
        $commodityPayment->is_authorized = $request->repayment_mode == 'savings' ? 1 : 0;
        $commodityPayment->ippis         = $ippis;
        $commodityPayment->pay_point     = $member->pay_point;
        $commodityPayment->ref           = $request->ref;
        $commodityPayment->deposit_date  = $request->deposit_date;
        $commodityPayment->commodity_id  = $CommodityRecord->id;
        $commodityPayment->dr            = 0.00;
        $commodityPayment->cr            = $request->total_amount;
        $commodityPayment->bal           = $commodity_bal;
        $commodityPayment->month         = Carbon::today()->format('m');
        $commodityPayment->year          = Carbon::today()->format('Y');
        $commodityPayment->done_by       = auth()->user()->ippis;
        $commodityPayment->save();


        // make ledger entry
        $ledger                 = new Ledger;
        $ledger->trxn_number    = $trxnNumber;
        $ledger->trxn_type      = 'coml_Rp_Deposit';
        $ledger->is_authorized  = $request->repayment_mode == 'savings' ? 1 : 0;
        $ledger->member_id      = $member->id;
        $ledger->pay_point      = $member->pay_point;
        $ledger->date           = Carbon::today()->format('Y-m-d');
        $ledger->ref            = $request->ref;
        $ledger->deposit_date   = $request->deposit_date;
        $ledger->savings_dr     = 0.00;
        $ledger->savings_cr     = 0.00;
        $ledger->savings_bal    = $member->savingsBalance();
        $ledger->long_term_dr   = 0.00;
        $ledger->long_term_cr   = 0.00;
        $ledger->long_term_bal  = $member->longTermLoanBalance();
        $ledger->short_term_dr  = 0.00;
        $ledger->short_term_cr  = 0.00;
        $ledger->short_term_bal = $member->shortTermLoanBalance();
        $ledger->commodity_dr   = 0.00;
        $ledger->commodity_cr   = $request->total_amount;
        $ledger->commodity_bal  = $commodity_bal;
        $ledger->done_by        = auth()->user()->ippis;
        $ledger->save();

        $activityLog = new ActivityLog;
        $activityLog->logThis($trxnNumber, $member->ippis, '(Comm LOAN) '.$request->ref, $request->total_amount, $request->repayment_mode == 'savings' ? 1 : 0, auth()->user()->ippis);

        // dd($stl, $ledger);

        Toastr::success('Repayent successful', 'Success', ["positionClass" => "toast-bottom-right"]);
        return redirect()->route('members.commodity', $ippis);

    }


    function loanDetails($loanID) {
        $data['loan'] = Commodity::find($loanID);

        return view('members.commodity.loan_details', $data);
    }
}
