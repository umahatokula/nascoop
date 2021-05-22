<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Member;
use App\Ledger;
use App\MonthlySaving;
use App\MonthlySavingsPayment;
use App\LongTerm;
use App\LongTermPayment;
use App\ShortTerm;
use App\ShortTermPayment;
use App\Commodity;
use App\CommodityPayment;
use Carbon\Carbon;
use App\ActivityLog;
use App\Helpers\CurrencyInWords;
use App\ProcessingFee;
use App\PVNumber;
use App\LoanDuration;
use App\Ledger_Internal;
use App\ManualLedgerPosting;
use App\TransactionType_Ext;
use App\Events\TransactionOccured;
use Toastr;
use DB; 

class ManualLedgerPostingsController extends Controller
{
    public function __construct() {
        $this->middleware(['auth']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

        $dateFrom = Carbon::today()->startOfYear();
        $dateTo = Carbon::today()->endOfYear();

        if (request('dateFrom')) {
            $dateFrom = Carbon::parse(request('dateFrom'));
            $dateTo = Carbon::parse(request('dateTo'));
        }

        $postingsQuery = ManualLedgerPosting::query();
        $postingsQuery = $postingsQuery->whereBetween('created_at', [$dateFrom, $dateTo]);

        $data['postings'] = $postingsQuery->paginate(20);
        $data['dateFrom'] = $dateFrom;
        $data['dateTo'] = $dateTo;

        return view('manual_ledger_postings.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $data['members'] = Member::select('ippis', DB::raw('CONCAT(full_name, " - ", ippis) AS name'))->pluck('name', 'ippis');
		
        $bankParents = Ledger_Internal::where('ledger_no', 121000)->first();
        $data['banks'] = $bankParents->getChildren()->pluck('account_name', 'ledger_no');

        $data['accounts'] = [
            'savings' => 'Monthly Savings',
            'ltl' => 'Long Term Loan',
            'stl' => 'Short Term Loan',
            'coml' => 'Commodity Loan',
        ];

        $data['debit_accounts'] = [
            'ltl' => 'Long Term Loan',
            'stl' => 'Short Term Loan',
            'coml' => 'Commodity Loan',
        ];

        $data['credit_accounts'] = [
            'savings' => 'Monthly Savings',
        ];

        return view('manual_ledger_postings.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        // dd($request->all());

        $rules = [
            'ippis' => 'required',
            'amount' => 'required',
            'debit_account' => 'required | different:credit_account',
            'credit_account' => 'required',
            'description' => 'required',
            // 'bank' => 'required',

        ];

        $messages = [
            'ippis.required' => 'Select a member',
            'amount.date_format' => 'Enter an amount',
            'debit_account.required' => 'Select a debit account',
            'credit_account.required' => 'Select a credit account',
            'description.required' => 'Enter a description',
        ];

        $this->validate($request, $rules, $messages);

        $ippis          = $request->ippis;
        $debit_account  = $request->debit_account;
        $credit_account = $request->credit_account;
        $amount         = $request->amount;
        $description    = $request->description;
        $bank           = $request->bank;
        $value_date     = $request->value_date;

        $member = Member::where('ippis', $ippis)->first();

        if($member->hasPendingTransaction()) {
            flash('Please process all pending transactions first')->error();
            return redirect()->back();
        }

        // generate a code to be used for this ledger entry
        $ledger = new Ledger;
        $trxnNumber = $ledger->generateTrxnNumber();

        $mlp = new ManualLedgerPosting;
        $mlp->ippis          = $ippis;
        $mlp->trxn_number    = $trxnNumber;
        $mlp->debit_account  = $debit_account;
        $mlp->credit_account = $credit_account;
        $mlp->amount         = $amount;
        $mlp->description    = $description;
        $mlp->bank           = $bank;
        $mlp->value_date     = $value_date;
        $mlp->done_by        = auth()->user()->ippis;
        $mlp->save();

        return redirect('manual-ledger-postings');

    }

    /**
     * Approve
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approve($id) {

        $posting = ManualLedgerPosting::find($id);
        $posting->is_authorized = 1;
        $posting->save();

        $ippis = $posting->ippis;
        $trxnNumber = $posting->trxn_number;

        $member = Member::where('ippis', $posting->ippis)->first();


        if ($posting->debit_account == 'savings') {

            $this->debitSavings($ippis, $trxnNumber, $posting);

            $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'dp_S')->first();
            $debit = $trxnType->getDetailAccountForThisTransactionType('dr', $member->member_pay_point ? $member->member_pay_point->name : '');
        }

        if ($posting->credit_account == 'savings') {

            $this->creditSavings($ippis, $trxnNumber, $posting);

            $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'dp_S')->first();
            $credit = $trxnType->getDetailAccountForThisTransactionType('cr', $member->member_pay_point ? $member->member_pay_point->name : '');
        }




        if ($posting->debit_account == 'ltl') {

            $this->debitLTL($ippis, $trxnNumber, $posting);

            $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'ltl')->first();
            $debit = $trxnType->getDetailAccountForThisTransactionType('dr', $member->member_pay_point ? $member->member_pay_point->name : '');
        }

        if ($posting->credit_account == 'ltl') {

            $this->creditLTL($ippis, $trxnNumber, $posting);

            $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'ltl')->first();
            $credit = $trxnType->getDetailAccountForThisTransactionType('cr', $member->member_pay_point ? $member->member_pay_point->name : '');
        }




        if ($posting->debit_account == 'stl') {

            $this->debitSTL($ippis, $trxnNumber, $posting);

            $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'stl')->first();
            $debit = $trxnType->getDetailAccountForThisTransactionType('dr', $member->member_pay_point ? $member->member_pay_point->name : '');
        }

        if ($posting->credit_account == 'stl') {

            $this->creditSTL($ippis, $trxnNumber, $posting);

            $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'stl')->first();
            $credit = $trxnType->getDetailAccountForThisTransactionType('cr', $member->member_pay_point ? $member->member_pay_point->name : '');
            dd($credit);
        }




        if ($posting->debit_account == 'coml') {

            $this->debitCOML($ippis, $trxnNumber, $posting);

            $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'coml')->first();
            $debit = $trxnType->getDetailAccountForThisTransactionType('dr', $member->member_pay_point ? $member->member_pay_point->name : '');
        }

        if ($posting->credit_account == 'coml') {

            $this->creditCOML($ippis, $trxnNumber, $posting);

            $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'coml')->first();
            $credit = $trxnType->getDetailAccountForThisTransactionType('cr', $member->member_pay_point ? $member->member_pay_point->name : '');
        }




        event(new TransactionOccured($credit, $debit, microtime(), 'cr', 'dp_S', $member->ippis, 'saving', $posting->amount, $posting->description, $posting->value_date));


        flash('Approved')->success();
        return redirect()->back();
    }

    /**
     * Approve
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function disapprove($id) {
        $posting = ManualLedgerPosting::find($id);
        $posting->is_authorized = 2;
        $posting->save();

        flash('Disapproved')->error();
        return redirect()->back();
    }


    /**
     * Add to savings
     */
    function creditSavings($ippis, $trxnNumber, ManualLedgerPosting $mlp) {
        $member = Member::where('ippis', $ippis)->first();
        
        if ($member->is_active == 0) {
            Toastr::error('This member has been deactivated', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.dashboard', $member->ippis);
        }

        $lastMonthlySavingRecord = $member->latest_monthly_saving();

        if (!$lastMonthlySavingRecord) {
            Toastr::error('Please set the monthly contribution amount first', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->back();
        }

        if(isset($lastMonthlySavingRecord)) {
            $lastMonthlySavingsPaymentRecord = $member->latest_monthly_savings_payment();
            
            $savings_bal = isset($lastMonthlySavingsPaymentRecord) ? $lastMonthlySavingsPaymentRecord->bal + $mlp->amount : $mlp->amount;

        } else {

            $savings_bal = $mlp->amount;

        }
        
        // make entry in monthly savings payments table
        $msPayment                    = new MonthlySavingsPayment;
        $msPayment->trxn_number       = $trxnNumber;
        $msPayment->trxn_type         = 'dp_S';
        $msPayment->is_authorized     = 1;
        $msPayment->monthly_saving_id = $lastMonthlySavingRecord->id;
        $msPayment->ippis             = $ippis;
        $msPayment->pay_point         = $member->pay_point;
        $msPayment->deposit_date      = $mlp->created_at;
        $msPayment->ref               = $mlp->description;
        $msPayment->dr                = $mlp->debit_account == 'savings' ? $mlp->amount : 0.00;
        $msPayment->cr                = $mlp->amount;
        $msPayment->bal               = $savings_bal;
        $msPayment->month             = Carbon::today()->format('m');
        $msPayment->year              = Carbon::today()->format('Y');
        $msPayment->done_by           = auth()->user()->ippis;
        $msPayment->save();


        // make ledger entry
        $lasLedger = Ledger::where('member_id', $member->id)->latest('id')->first();
        $long_term_dr   = $mlp->debit_account == 'ltl' ? $lasLedger->long_term_dr : 0.00;
        $long_term_cr   = $mlp->debit_account == 'ltl' ? $lasLedger->long_term_cr : 0.00;
        $long_term_bal  = $lasLedger->long_term_bal;
        $short_term_dr  = $mlp->debit_account == 'stl' ? $lasLedger->short_term_dr : 0.00;
        $short_term_cr  = $mlp->debit_account == 'stl' ? $lasLedger->short_term_cr : 0.00;
        $short_term_bal = $lasLedger->short_term_bal;
        $commodity_dr   = $mlp->debit_account == 'coml' ? $lasLedger->commodity_dr : 0.00;
        $commodity_cr   = $mlp->debit_account == 'coml' ? $lasLedger->commodity_cr : 0.00;
        $commodity_bal  = $lasLedger->commodity_bal;

        $ledger = Ledger::where('trxn_number', $trxnNumber)->first();$long_term_dr   = 0.00;

        if(!$ledger) {
            $ledger = new Ledger;
        }
        
        $ledger->trxn_number    = $trxnNumber;
        $ledger->trxn_type      = 'dp_S';
        $ledger->is_authorized  = 1;
        $ledger->member_id      = $member->id;
        $ledger->pay_point      = $member->pay_point;
        $ledger->date           = $mlp->created_at;
        $ledger->ref            = $mlp->description;
        $ledger->savings_dr     = $mlp->debit_account == 'savings' ? $mlp->amount : 0.00;
        $ledger->savings_cr     = $mlp->amount;
        $ledger->savings_bal    = $savings_bal;
        $ledger->long_term_dr   = $long_term_dr;
        $ledger->long_term_cr   = $long_term_cr;
        $ledger->long_term_bal  = $long_term_bal;
        $ledger->short_term_dr  = $short_term_dr;
        $ledger->short_term_cr  = $short_term_cr;
        $ledger->short_term_bal = $short_term_bal;
        $ledger->commodity_dr   = $commodity_dr;
        $ledger->commodity_cr   = $commodity_cr;
        $ledger->commodity_bal  = $commodity_bal;
        $ledger->done_by        = auth()->user()->ippis;
        $ledger->save();

        return ;
    }

    /**
     * Post withdrawal
     */
    function debitSavings($ippis, $trxnNumber, ManualLedgerPosting $mlp) {
        $member = Member::where('ippis', $ippis)->first();
        
        if ($member->is_active == 0) {
            Toastr::error('This member has been deactivated', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.dashboard', $member->ippis);
        }

        $lastMonthlySavingRecord = $member->latest_monthly_saving();

        if (!$lastMonthlySavingRecord) {
            Toastr::error('Please set the monthly contribution amount first', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->back();
        }

        if(isset($lastMonthlySavingRecord)) {
            $lastMonthlySavingsPaymentRecord = $member->latest_monthly_savings_payment();
            
            $savings_bal = isset($lastMonthlySavingsPaymentRecord) ? $lastMonthlySavingsPaymentRecord->bal + $mlp->amount : $mlp->amount;

        } else {

            $savings_bal = $mlp->amount;

        }
        
        // make entry in monthly savings payments table
        $msPayment                    = new MonthlySavingsPayment;
        $msPayment->trxn_number       = $trxnNumber;
        $msPayment->trxn_type         = 'dp_S';
        $msPayment->is_authorized     = 1;
        $msPayment->monthly_saving_id = $lastMonthlySavingRecord->id;
        $msPayment->ippis             = $ippis;
        $msPayment->pay_point         = $member->pay_point;
        $msPayment->deposit_date      = $mlp->created_at;
        $msPayment->ref               = $mlp->description;
        $msPayment->dr                = $mlp->amount;
        $msPayment->cr                = $mlp->credit_account == 'savings' ? $mlp->amount : 0.00;
        $msPayment->bal               = $savings_bal;
        $msPayment->month             = Carbon::today()->format('m');
        $msPayment->year              = Carbon::today()->format('Y');
        $msPayment->done_by           = auth()->user()->ippis;
        $msPayment->save();


        // make ledger entry
        $lasLedger = Ledger::where('member_id', $member->id)->latest('id')->first();
        $long_term_dr   = $mlp->debit_account == 'ltl' ? $lasLedger->long_term_dr : 0.00;
        $long_term_cr   = $mlp->debit_account == 'ltl' ? $lasLedger->long_term_cr : 0.00;
        $long_term_bal  = $lasLedger->long_term_bal;
        $short_term_dr  = $mlp->debit_account == 'stl' ? $lasLedger->short_term_dr : 0.00;
        $short_term_cr  = $mlp->debit_account == 'stl' ? $lasLedger->short_term_cr : 0.00;
        $short_term_bal = $lasLedger->short_term_bal;
        $commodity_dr   = $mlp->debit_account == 'coml' ? $lasLedger->commodity_dr : 0.00;
        $commodity_cr   = $mlp->debit_account == 'coml' ? $lasLedger->commodity_cr : 0.00;
        $commodity_bal  = $lasLedger->commodity_bal;

        $ledger = Ledger::where('trxn_number', $trxnNumber)->first();$long_term_dr   = 0.00;

        if(!$ledger) {
            $ledger = new Ledger;
        }
        
        $ledger->trxn_number    = $trxnNumber;
        $ledger->trxn_type      = 'dp_S';
        $ledger->is_authorized  = 1;
        $ledger->member_id      = $member->id;
        $ledger->pay_point      = $member->pay_point;
        $ledger->date           = $mlp->created_at;
        $ledger->ref            = $mlp->description;
        $ledger->savings_dr     = $mlp->amount;
        $ledger->savings_cr     = $mlp->credit_account == 'savings' ? $mlp->amount : 0.00;
        $ledger->savings_bal    = $savings_bal;
        $ledger->long_term_dr   = $long_term_dr;
        $ledger->long_term_cr   = $long_term_cr;
        $ledger->long_term_bal  = $long_term_bal;
        $ledger->short_term_dr  = $short_term_dr;
        $ledger->short_term_cr  = $short_term_cr;
        $ledger->short_term_bal = $short_term_bal;
        $ledger->commodity_dr   = $commodity_dr;
        $ledger->commodity_cr   = $commodity_cr;
        $ledger->commodity_bal  = $commodity_bal;
        $ledger->done_by        = auth()->user()->ippis;
        $ledger->save();

        return ;
    }

    /**
     * Debit LTL
     */
    function debitLTL($ippis, $trxnNumber, ManualLedgerPosting $mlp) {

        $member = Member::where('ippis', $ippis)->first();

        // MAKE ENTRY FOR NEW LOAN BEING TAKEN
        //
        // get last long term loan
        $longLongTermRecord = $member->latest_long_term_loan();

        $longLongTermPaymentRecord = $member->latest_long_term_payment();

        $ltlPayment                = new LongTermPayment;
        $ltlPayment->trxn_number    = $trxnNumber;
        $ltlPayment->trxn_type      = 'ltl';
        $ltlPayment->is_authorized = 1;
        $ltlPayment->ippis         = $ippis;
        $ltlPayment->pay_point     = $member->pay_point;
        $ltlPayment->ref           = $mlp->description;
        $ltlPayment->loan_date     = $mlp->created_at;
        $ltlPayment->long_term_id  = $longLongTermRecord->id;
        $ltlPayment->dr            = $mlp->amount;
        $ltlPayment->cr            = $mlp->credit_account == 'ltl' ? $mlp->amount : 0.00;
        $ltlPayment->bal           = $longLongTermPaymentRecord->bal + $mlp->amount;
        $ltlPayment->month         = Carbon::today()->format('m');
        $ltlPayment->year          = Carbon::today()->format('Y');
        $ltlPayment->done_by       = auth()->user()->ippis;
        $ltlPayment->save();


        // make ledger entry
        $lasLedger = Ledger::where('member_id', $member->id)->latest('id')->first();
        $savings_dr     = $mlp->credit_account == 'savings' ? $lasLedger->savings_dr : 0.00;
        $savings_cr     = $mlp->credit_account == 'savings' ? $lasLedger->savings_cr : 0.00;
        $savings_bal    = $lasLedger->savings_bal;
        $short_term_dr  = $mlp->credit_account == 'stl' ? $lasLedger->short_term_dr : 0.00;
        $short_term_cr  = $mlp->credit_account == 'stl' ? $lasLedger->short_term_cr : 0.00;
        $short_term_bal = $lasLedger->short_term_bal;
        $commodity_dr   = $mlp->credit_account == 'coml' ? $lasLedger->commodity_dr : 0.00;
        $commodity_cr   = $mlp->credit_account == 'coml' ? $lasLedger->commodity_cr : 0.00;
        $commodity_bal  = $lasLedger->commodity_bal;

        $ledger = Ledger::where('trxn_number', $trxnNumber)->first();
        
        if(!$ledger) {
            $ledger = new Ledger;
        }
        
        $ledger->trxn_number    = $trxnNumber;
        $ledger->trxn_type      = 'ltl';
        $ledger->is_authorized  = 1;
        $ledger->member_id      = $member->id;
        $ledger->pay_point      = $member->pay_point;
        $ledger->date           = $mlp->created_at;
        $ledger->ref            = $mlp->description;
        $ledger->loan_date      = $mlp->created_at;
        $ledger->savings_dr     = $savings_dr;
        $ledger->savings_cr     = $savings_cr;
        $ledger->savings_bal    = $savings_bal;
        $ledger->long_term_dr   = $mlp->amount;
        $ledger->long_term_cr   = $mlp->credit_account == 'ltl' ? $mlp->amount : 0.00;
        $ledger->long_term_bal  = $longLongTermPaymentRecord->bal + $mlp->amount;
        $ledger->short_term_dr  = $short_term_dr;
        $ledger->short_term_cr  = $short_term_cr;
        $ledger->short_term_bal = $short_term_bal;
        $ledger->commodity_dr   = $commodity_dr;
        $ledger->commodity_cr   = $commodity_cr;
        $ledger->commodity_bal  = $commodity_bal;
        $ledger->done_by        = auth()->user()->ippis;
        $ledger->save();

        return ;
    }


    /**
     * Credit LTL
     */
    function creditLTL($ippis, $trxnNumber, ManualLedgerPosting $mlp) {
        $member = Member::where('ippis', $ippis)->first();
        
        $longLongTermRecord = $member->latest_long_term_loan();
            
        $longLongTermPaymentRecord = $member->latest_long_term_payment();

        // make entry in long term payments table
        $ltlPayment                = new LongTermPayment;
        $ltlPayment->trxn_number   = $trxnNumber;
        $ltlPayment->trxn_type     = 'ltl_Rp_Savings';
        $ltlPayment->is_authorized = 1;
        $ltlPayment->ippis         = $ippis;
        $ltlPayment->pay_point     = $member->pay_point;
        $ltlPayment->ref           = $mlp->description;
        $ltlPayment->deposit_date  = $mlp->created_at;
        $ltlPayment->long_term_id  = $longLongTermRecord->id;
        $ltlPayment->dr            = $mlp->debit_account == 'ltl' ? $mlp->amount : 0.00;
        $ltlPayment->cr            = $mlp->amount;
        $ltlPayment->bal           = $longLongTermPaymentRecord->bal;
        $ltlPayment->month         = Carbon::today()->format('m');
        $ltlPayment->year          = Carbon::today()->format('Y');
        $ltlPayment->done_by       = auth()->user()->ippis;
        $ltlPayment->save();


        // make ledger entry
        $lasLedger = Ledger::where('member_id', $member->id)->latest('id')->first();
        $savings_dr     = $mlp->debit_account == 'savings' ? $lasLedger->savings_dr : 0.00;
        $savings_cr     = $mlp->debit_account == 'savings' ? $lasLedger->savings_cr : 0.00;
        $savings_bal    = $lasLedger->savings_bal;
        $short_term_dr  = $mlp->debit_account == 'stl' ? $lasLedger->short_term_dr : 0.00;
        $short_term_cr  = $mlp->debit_account == 'stl' ? $lasLedger->short_term_cr : 0.00;
        $short_term_bal = $lasLedger->short_term_bal;
        $commodity_dr   = $mlp->debit_account == 'coml' ? $lasLedger->commodity_dr : 0.00;
        $commodity_cr   = $mlp->debit_account == 'coml' ? $lasLedger->commodity_cr : 0.00;
        $commodity_bal  = $lasLedger->commodity_bal;

        $ledger = Ledger::where('trxn_number', $trxnNumber)->first();
        
        if(!$ledger) {
            $ledger = new Ledger;
        }
        
        $ledger->trxn_number    = $trxnNumber;
        $ledger->trxn_type      = 'ltl_Rp_Savings';
        $ledger->is_authorized  = 1;
        $ledger->member_id      = $member->id;
        $ledger->pay_point      = $member->pay_point;
        $ledger->date           = Carbon::today()->format('Y-m-d');
        $ledger->ref            = $mlp->description;
        $ledger->deposit_date   = $mlp->created_at;
        $ledger->savings_dr     = $savings_dr;
        $ledger->savings_cr     = $savings_cr;
        $ledger->savings_bal    = $savings_bal;
        $ledger->long_term_dr   = $mlp->debit_account == 'ltl' ? $mlp->amount : 0.00;
        $ledger->long_term_cr   = $mlp->amount;
        $ledger->long_term_bal  = $longLongTermPaymentRecord->bal - $mlp->amount;
        $ledger->short_term_dr  = $short_term_dr;
        $ledger->short_term_cr  = $short_term_cr;
        $ledger->short_term_bal = $short_term_bal;
        $ledger->commodity_dr   = $commodity_dr;
        $ledger->commodity_cr   = $commodity_cr;
        $ledger->commodity_bal  = $commodity_bal;
        $ledger->done_by        = auth()->user()->ippis;
        $ledger->save();
    }

    /**
     * Debit STL
     */
    function debitSTL($ippis, $trxnNumber, ManualLedgerPosting $mlp) {

        $member = Member::where('ippis', $ippis)->first();

        // MAKE ENTRY FOR NEW LOAN BEING TAKEN
        //
        // get last long term loan
        $shortTermRecord = $member->latest_short_term_loan();

        $shortTermPaymentRecord = $member->latest_short_term_payment();

        $stlPayment = new ShortTermPayment;
        $stlPayment->trxn_number   = $trxnNumber;
        $stlPayment->trxn_type     = 'stl';
        $stlPayment->is_authorized = 1;
        $stlPayment->ippis         = $ippis;
        $stlPayment->pay_point     = $member->pay_point;
        $stlPayment->ref           = $mlp->description;
        $stlPayment->loan_date     = $mlp->created_at;
        $stlPayment->short_term_id = $shortTermRecord->id;
        $stlPayment->dr            = $mlp->amount;
        $stlPayment->cr            = $mlp->credit_account == 'stl' ? $mlp->amount : 0.00;
        $stlPayment->bal           = $shortTermPaymentRecord->bal + $mlp->amount;
        $stlPayment->month         = Carbon::today()->format('m');
        $stlPayment->year          = Carbon::today()->format('Y');
        $stlPayment->done_by       = auth()->user()->ippis;
        $stlPayment->save();


        // make ledger entry
        $lasLedger = Ledger::where('member_id', $member->id)->latest('id')->first();
        $savings_dr    = $mlp->credit_account == 'savings' ? $lasLedger->savings_dr : 0.00;
        $savings_cr    = $mlp->credit_account == 'savings' ? $lasLedger->savings_cr : 0.00;
        $savings_bal   = $lasLedger->savings_bal;
        $long_term_dr  = $mlp->credit_account == 'ltl' ? $lasLedger->long_term_dr : 0.00;
        $long_term_cr  = $mlp->credit_account == 'ltl' ? $lasLedger->long_term_cr : 0.00;
        $long_term_bal = $lasLedger->long_term_bal;
        $commodity_dr  = $mlp->credit_account == 'coml' ? $lasLedger->commodity_dr : 0.00;
        $commodity_cr  = $mlp->credit_account == 'coml' ? $lasLedger->commodity_cr : 0.00;
        $commodity_bal = $lasLedger->commodity_bal;


        $ledger = Ledger::where('trxn_number', $trxnNumber)->first();        
        if(!$ledger) {
            $ledger = new Ledger;
        }
        
        $ledger->trxn_number    = $trxnNumber;
        $ledger->trxn_type      = 'stl';
        $ledger->is_authorized  = 1;
        $ledger->member_id      = $member->id;
        $ledger->pay_point      = $member->pay_point;
        $ledger->date           = $mlp->created_at;
        $ledger->ref            = $mlp->description;
        $ledger->loan_date      = $mlp->created_at;
        $ledger->savings_dr     = $savings_dr;
        $ledger->savings_cr     = $savings_cr;
        $ledger->savings_bal    = $savings_bal;
        $ledger->long_term_dr   = $long_term_dr;
        $ledger->long_term_cr   = $long_term_cr;
        $ledger->long_term_bal  = $long_term_bal;
        $ledger->short_term_dr  = $mlp->amount;
        $ledger->short_term_cr  = $mlp->credit_account == 'stl' ? $mlp->amount : 0.00;
        $ledger->short_term_bal = $shortTermPaymentRecord->bal + $mlp->amount;
        $ledger->commodity_dr   = $commodity_dr;
        $ledger->commodity_cr   = $commodity_cr;
        $ledger->commodity_bal  = $commodity_bal;

        $ledger->done_by        = auth()->user()->ippis;
        $ledger->save();

        return ;
    }


    /**
     * Credit STL
     */
    function creditSTL($ippis, $trxnNumber, ManualLedgerPosting $mlp) {

        $member = Member::where('ippis', $ippis)->first();

        // MAKE ENTRY FOR NEW LOAN BEING TAKEN
        //
        // get last long term loan
        $shortTermRecord = $member->latest_short_term_loan();

        $shortTermPaymentRecord = $member->latest_short_term_payment();

        $stlPayment = new ShortTermPayment;
        $stlPayment->trxn_number   = $trxnNumber;
        $stlPayment->trxn_type     = 'stl';
        $stlPayment->is_authorized = 1;
        $stlPayment->ippis         = $ippis;
        $stlPayment->pay_point     = $member->pay_point;
        $stlPayment->ref           = $mlp->description;
        $stlPayment->loan_date     = $mlp->created_at;
        $stlPayment->short_term_id = $shortTermRecord->id;
        $stlPayment->dr            = $mlp->debit_account == 'stl' ? $mlp->amount : 0.00;
        $stlPayment->cr            = $mlp->amount;
        $stlPayment->bal           = $shortTermPaymentRecord->bal + $mlp->amount;
        $stlPayment->month         = Carbon::today()->format('m');
        $stlPayment->year          = Carbon::today()->format('Y');
        $stlPayment->done_by       = auth()->user()->ippis;
        $stlPayment->save();


        // make ledger entry
        $lasLedger = Ledger::where('member_id', $member->id)->latest('id')->first();
        $savings_dr    = $mlp->debit_account == 'savings' ? $lasLedger->savings_dr : 0.00;
        $savings_cr    = $mlp->debit_account == 'savings' ? $lasLedger->savings_cr : 0.00;
        $savings_bal   = $lasLedger->savings_bal;
        $long_term_dr  = $mlp->debit_account == 'ltl' ? $lasLedger->long_term_dr : 0.00;
        $long_term_cr  = $mlp->debit_account == 'ltl' ? $lasLedger->long_term_cr : 0.00;
        $long_term_bal = $lasLedger->long_term_bal;
        $commodity_dr  = $mlp->debit_account == 'coml' ? $lasLedger->commodity_dr : 0.00;
        $commodity_cr  = $mlp->debit_account == 'coml' ? $lasLedger->commodity_cr : 0.00;
        $commodity_bal = $lasLedger->commodity_bal;


        $ledger = Ledger::where('trxn_number', $trxnNumber)->first();
        
        if(!$ledger) {
            $ledger = new Ledger;
        }
        
        $ledger->trxn_number    = $trxnNumber;
        $ledger->trxn_type      = 'stl';
        $ledger->is_authorized  = 1;
        $ledger->member_id      = $member->id;
        $ledger->pay_point      = $member->pay_point;
        $ledger->date           = $mlp->created_at;
        $ledger->ref            = $mlp->description;
        $ledger->loan_date      = $mlp->created_at;
        $ledger->savings_dr     = $savings_dr;
        $ledger->savings_cr     = $savings_cr;
        $ledger->savings_bal    = $savings_bal;
        $ledger->long_term_dr   = $long_term_dr;
        $ledger->long_term_cr   = $long_term_cr;
        $ledger->long_term_bal  = $long_term_bal;
        $ledger->short_term_dr  = $mlp->debit_account == 'stl' ? $mlp->amount : 0.00;
        $ledger->short_term_cr  = $mlp->amount;
        $ledger->short_term_bal = $shortTermPaymentRecord->bal - $mlp->amount;
        $ledger->commodity_dr   = $commodity_dr;
        $ledger->commodity_cr   = $commodity_cr;
        $ledger->commodity_bal  = $commodity_bal;

        $ledger->done_by        = auth()->user()->ippis;
        $ledger->save();

        return ;
    }

    /**
     * Debit COML
     */
    function debitCOML($ippis, $trxnNumber, ManualLedgerPosting $mlp) {

        $member = Member::where('ippis', $ippis)->first();

        // MAKE ENTRY FOR NEW LOAN BEING TAKEN
        //
        // get last long term loan
        $commodityRecord = $member->latest_commodity_loan();

        $commodityPaymentRecord = $member->latest_commodities_payment();

        $comlPayment = new CommodityPayment;
        $comlPayment->trxn_number   = $trxnNumber;
        $comlPayment->trxn_type     = 'coml';
        $comlPayment->is_authorized = 1;
        $comlPayment->ippis         = $ippis;
        $comlPayment->pay_point     = $member->pay_point;
        $comlPayment->ref           = $mlp->description;
        $comlPayment->loan_date     = $mlp->created_at;
        $comlPayment->commodity_id = $commodityRecord->id;
        $comlPayment->dr            = $mlp->amount;
        $comlPayment->cr            = $mlp->credit_account == 'coml' ? $mlp->amount : 0.00;
        $comlPayment->bal           = $commodityPaymentRecord->bal + $mlp->amount;
        $comlPayment->month         = Carbon::today()->format('m');
        $comlPayment->year          = Carbon::today()->format('Y');
        $comlPayment->done_by       = auth()->user()->ippis;
        $comlPayment->save();


        // make ledger entry
        $lasLedger = Ledger::where('member_id', $member->id)->latest('id')->first();
        $savings_dr     = $mlp->credit_account == 'savings' ?$lasLedger->savings_dr : 0.00;
        $savings_cr     = $mlp->credit_account == 'savings' ?$lasLedger->savings_cr : 0.00;
        $savings_bal    = $lasLedger->savings_bal;
        $long_term_dr   = $mlp->credit_account == 'ltl' ?$lasLedger->long_term_dr : 0.00;
        $long_term_cr   = $mlp->credit_account == 'ltl' ?$lasLedger->long_term_cr : 0.00;
        $long_term_bal  = $lasLedger->long_term_bal;
        $short_term_dr  = $mlp->credit_account == 'stl' ?$lasLedger->short_term_dr : 0.00;
        $short_term_cr  = $mlp->credit_account == 'stl' ?$lasLedger->short_term_cr : 0.00;
        $short_term_bal = $lasLedger->short_term_bal;


        $ledger = Ledger::where('trxn_number', $trxnNumber)->first();        
        if(!$ledger) {
            $ledger = new Ledger;
        }
        
        $ledger->trxn_number    = $trxnNumber;
        $ledger->trxn_type      = 'coml';
        $ledger->is_authorized  = 1;
        $ledger->member_id      = $member->id;
        $ledger->pay_point      = $member->pay_point;
        $ledger->date           = $mlp->created_at;
        $ledger->ref            = $mlp->description;
        $ledger->loan_date      = $mlp->created_at;
        $ledger->savings_dr     = $savings_dr;
        $ledger->savings_cr     = $savings_cr;
        $ledger->savings_bal    = $savings_bal;
        $ledger->long_term_dr   = $long_term_dr;
        $ledger->long_term_cr   = $long_term_cr;
        $ledger->long_term_bal  = $long_term_bal;
        $ledger->short_term_dr  = $short_term_dr;
        $ledger->short_term_cr  = $short_term_cr;
        $ledger->short_term_bal = $short_term_bal;
        $ledger->commodity_dr   = $mlp->amount;
        $ledger->commodity_cr   = $mlp->credit_account == 'coml' ? $mlp->amount : 0.00;
        $ledger->commodity_bal  = $commodityPaymentRecord->bal + $mlp->amount;

        $ledger->done_by        = auth()->user()->ippis;
        $ledger->save();

        return ;
    }


    /**
     * Credit COML
     */
    function creditCOML($ippis, $trxnNumber, ManualLedgerPosting $mlp) {

        $member = Member::where('ippis', $ippis)->first();

        // MAKE ENTRY FOR NEW LOAN BEING TAKEN
        //
        // get last long term loan
        $commodityRecord = $member->latest_commodity_loan();

        $commodityPaymentRecord = $member->latest_commodities_payment();

        $comlPayment = new CommodityPayment;
        $comlPayment->trxn_number   = $trxnNumber;
        $comlPayment->trxn_type     = 'coml';
        $comlPayment->is_authorized = 1;
        $comlPayment->ippis         = $ippis;
        $comlPayment->pay_point     = $member->pay_point;
        $comlPayment->ref           = $mlp->description;
        $comlPayment->loan_date     = $mlp->created_at;
        $comlPayment->commodity_id = $commodityRecord->id;
        $comlPayment->dr            = $mlp->debit_account == 'coml' ? $mlp->amount : 0.00;
        $comlPayment->cr            = $mlp->amount;
        $comlPayment->bal           = $commodityPaymentRecord->bal - $mlp->amount;
        $comlPayment->month         = Carbon::today()->format('m');
        $comlPayment->year          = Carbon::today()->format('Y');
        $comlPayment->done_by       = auth()->user()->ippis;
        $comlPayment->save();


        // make ledger entry
        $lasLedger = Ledger::where('member_id', $member->id)->latest('id')->first();
        $savings_dr     = $mlp->debit_account == 'savings' ? $lasLedger->savings_dr : 0.00;
        $savings_cr     = $mlp->debit_account == 'savings' ? $lasLedger->savings_cr : 0.00;
        $savings_bal    = $lasLedger->savings_bal;
        $long_term_dr   = $mlp->debit_account == 'ltl' ? $lasLedger->long_term_dr : 0.00;
        $long_term_cr   = $mlp->debit_account == 'ltl' ? $lasLedger->long_term_cr : 0.00;
        $long_term_bal  = $lasLedger->long_term_bal;
        $short_term_dr  = $mlp->debit_account == 'stl' ? $lasLedger->short_term_dr : 0.00;
        $short_term_cr  = $mlp->debit_account == 'stl' ? $lasLedger->short_term_cr : 0.00;
        $short_term_bal = $lasLedger->short_term_bal;


        $ledger = Ledger::where('trxn_number', $trxnNumber)->first();        
        if(!$ledger) {
            $ledger = new Ledger;
        }
        
        $ledger->trxn_number    = $trxnNumber;
        $ledger->trxn_type      = 'coml';
        $ledger->is_authorized  = 1;
        $ledger->member_id      = $member->id;
        $ledger->pay_point      = $member->pay_point;
        $ledger->date           = $mlp->created_at;
        $ledger->ref            = $mlp->description;
        $ledger->loan_date      = $mlp->created_at;
        $ledger->savings_dr     = $savings_dr;
        $ledger->savings_cr     = $savings_cr;
        $ledger->savings_bal    = $savings_bal;
        $ledger->long_term_dr   = $long_term_dr;
        $ledger->long_term_cr   = $long_term_cr;
        $ledger->long_term_bal  = $long_term_bal;
        $ledger->short_term_dr  = $short_term_dr;
        $ledger->short_term_cr  = $short_term_cr;
        $ledger->short_term_bal = $short_term_bal;
        $ledger->commodity_dr   = $mlp->debit_account == 'coml' ? $mlp->amount : 0.00;
        $ledger->commodity_cr   = $mlp->amount;
        $ledger->commodity_bal  = $commodityPaymentRecord->bal - $mlp->amount;

        $ledger->done_by        = auth()->user()->ippis;
        $ledger->save();

        return ;
    }
}
