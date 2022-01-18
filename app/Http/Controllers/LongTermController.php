<?php

namespace App\Http\Controllers;

use App\Member;
use App\Bank;
use App\LongTerm;
use App\LongTermPayment;
use App\Ledger;
use Carbon\Carbon;
use Toastr;
use App\MonthlySaving;
use App\MonthlySavingsPayment;
use App\Helpers\CurrencyInWords;
use App\ProcessingFee;
use App\PVNumber;
use App\ActivityLog;
use App\LoanDuration;
use App\Events\LoanRepayment;
use App\Events\WithdrawalFromSavings;
use App\Events\TransactionOccured;
use App\TransactionType_Ext;
use App\Ledger_Internal;

use Illuminate\Http\Request;

class LongTermController extends Controller
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
     * Long term loans
     */
    function longTermLoans(Request $request, $ippis) {

        $date_from = Carbon::now()->startOfYear();
        $date_to = Carbon::now()->endOfYear();
        $member = Member::where('ippis', $ippis)->first();
        $data['member'] = $member;

        if(!isset($member)) {
            Toastr::error('This member does not exist', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.longTermLoans', $ippis);
        }

        $longTermLoansQuery = LongTerm::query();
        $longTermLoansQuery = $longTermLoansQuery->with(['payments' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->where('ippis', $ippis);

        if ($request->date_from) {    
            $date_from = $request->date_from;
            $date_to = $request->date_to; 
        }
        
        $longTermLoansQuery = $longTermLoansQuery->whereBetween('loan_date', [$date_from, $date_to]);
        $longTermLoansPaymentQuery1 = LongTermPayment::whereBetween('loan_date', [$date_from, $date_to])->where('ippis', $ippis)->get();
        $longTermLoansPaymentQuery2 = LongTermPayment::whereBetween('deposit_date', [$date_from, $date_to])->where('ippis', $ippis)->get();

        $data['date_from'] = $date_from;
        $data['date_to'] = $date_to;
        $data['longTermLoans'] = $longTermLoansQuery->get();
        $data['payments'] = $longTermLoansPaymentQuery1->merge($longTermLoansPaymentQuery2)->sortBy('id');

        return view('members.long_term.long_term', $data);
    }

    /**
     * show new long term loans form
     */
    function newLongLoan($ippis) {
        // dd($ippis);

        $member = Member::where('ippis', $ippis)->first();
        
        if ($member->is_active == 0) {
            Toastr::error('This member has been deactivated', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.dashboard', $member->ippis);
        }

        $data['member'] = $member;

        // ensure certain bio details are entered
        $ensureMemberDetails = $member->ensureMemberDetails();
        if (!$ensureMemberDetails):            
            flash('To proceed, you must update the following details: Phone number, Email, Paypoint and Centre')->error();
            // return redirect()->route('editMember', $ippis);
        endif;

        /**
         * check if member has ever taken housing loan
         */
        // $takenHousingLoan = false;
        // // $housingLoan = LongTerm::where(['ippis' => $ippis])->where(['no_of_months' => 72])->orWhere(['no_of_months' => 36])->first();
        // $housingLoan = LongTerm::where('ippis', $ippis)
        //     ->where(function($query) {
        //         $query->orWhere('no_of_months', 72)
        //               ->orWhere('no_of_months', 36);
        //     })->first();

        // if ($housingLoan) {
        //     $housingLoanPaymentIsAuthorized = LongTermPayment::where(['long_term_id' => $housingLoan->id, 'is_authorized' => 1])->first();

        //     if ($housingLoanPaymentIsAuthorized) {
        //         $takenHousingLoan = true;
        //     }
        // }

        
        $takenHousingLoan36 = false;
        $takenHousingLoan72 = false;
        $housingLoan36 = LongTerm::where(['ippis' => $ippis])->where(['no_of_months' => 36, 'is_approved' => 1])->first();
        $housingLoan72 = LongTerm::where(['ippis' => $ippis])->where(['no_of_months' => 72, 'is_approved' => 1])->first();

        if ($housingLoan36) {
            $housing36LoanPaymentIsAuthorized = LongTermPayment::where(['long_term_id' => $housingLoan36->id, 'is_authorized' => 1])->first();

            if ($housing36LoanPaymentIsAuthorized) {
                $takenHousingLoan36 = true;
            }
        }

        if ($housingLoan72) {
            $housing72LoanPaymentIsAuthorized = LongTermPayment::where(['long_term_id' => $housingLoan72->id, 'is_authorized' => 1])->first();

            if ($housing72LoanPaymentIsAuthorized) {
                $takenHousingLoan72 = true;
            }
        }

        if(!isset($data['member'])) {
            Toastr::error('This member does not exist', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.longTermLoans', $ippis);
        }
        
        if(request()->ajax()) {
            $lastSavingsPayment = $member->latest_monthly_savings_payment();
            $lastLoanPayment = $member->latest_long_term_payment();
            $banks = Bank::all();
            $processingFee = ProcessingFee::first();

            $members = Member::all();

            $result = [];
            foreach ($members as $m) {
                $result[] = ['ippis' => $m->ippis, 'full_name' => $m->full_name.' ('.$m->ippis.')'.' ( N '.number_format($m->latest_monthly_savings_payment() ? $m->latest_monthly_savings_payment()->bal : 0, 2).')'];
            }
            $members = $result;
            
            $periods = LoanDuration::where('code', 'ltl')->get();

            return response()->json([
                'banks'                 => $banks,
                'processingFee'         => $processingFee,
                'savings'               => $lastSavingsPayment,
                'periods'               => $periods,
                'lastLoan'              => $lastLoanPayment,
                'members'               => json_encode($members),
                'member'                => $member,
                'taken_housing_loan_36' => $takenHousingLoan36,
                'taken_housing_loan_72' => $takenHousingLoan72,
            ]);
        }

        return view('members.long_term.new_long_term_loan', $data);
    }

    /**
     * save new long term loans
     */
    function postNewLongLoan(Request $request, $ippis) {
        // dd($request->all(), $ippis);

        $rules = [
            'ref' => 'required',
            'loan_date' => 'required',
            'no_of_months' => 'required|numeric',
            'ippis' => 'required',
            'total_amount' => 'numeric|required',
            'guarantor_1' => 'required',
            'guarantor_2' => 'required',
            'bank_charges' => 'required',
            // 'email' => 'email|unique:member,email',

        ];

        $messages = [
            'guarantor_1.required' => '1st guarantor is required',
            'guarantor_2.required' => '2nd guarantor is required',
            'ref.required' => 'The description is required',
            'loan_date.required' => 'The loan date is required',
            'no_of_months.required' => 'The number of months is required',
            'ippis.required' => 'This IPPIS Number is required',
            'total_amount.required' => 'Kindly enter the loan amount',
            'bank_charges.required' => 'Kindly select a bank',
        ];

        $this->validate($request, $rules, $messages);

        $member = Member::where('ippis', $ippis)->first();

        // generate a code to be used for this ledger entry
        $ledger = new Ledger;
        $trxnNumber = $ledger->generateTrxnNumber();

        // MAKE ENTRY FOR LOAN ADJUSTMENT
        if ($request->adjustment > 0) {

            $last_loan = $member->latest_long_term_loan();

            $last_loan_payment = $member->latest_long_term_payment();

            // make entry in long term payments table for loan adjustment
            $ltlPayment                = new LongTermPayment;
            $ltlPayment->trxn_number   = $trxnNumber;
            $ltlPayment->trxn_type     = 'ltl_Rp_Savings';
            $ltlPayment->is_authorized = 3;
            $ltlPayment->ippis         = $ippis;
            $ltlPayment->pay_point     = $member->pay_point;
            $ltlPayment->ref           = 'LOAN ADJUSTMENT';
            $ltlPayment->loan_date     = $request->loan_date;
            $ltlPayment->long_term_id  = $last_loan->id;
            $ltlPayment->dr            = 0.00;
            $ltlPayment->cr            = $request->adjustment;
            $ltlPayment->bal           = ($last_loan_payment->bal - $request->adjustment);
            $ltlPayment->month         = Carbon::today()->format('m');
            $ltlPayment->year          = Carbon::today()->format('Y');
            $ltlPayment->done_by       = auth()->user()->ippis;
            $ltlPayment->save();

            // make ledger entry for loan adjustment
            $ledger                 = new Ledger;
            $ledger->trxn_number    = $trxnNumber;
            $ledger->trxn_type      = 'ltl';
            $ledger->trxn_type      = 'ltl_Rp_Savings';
            $ledger->is_authorized  = 3;
            $ledger->member_id      = $member->id;
            $ledger->pay_point      = $member->pay_point;
            $ledger->date           = $request->loan_date;
            $ledger->ref            = 'LOAN ADJUSTMENT';
            $ledger->loan_date      = $request->loan_date;
            $ledger->savings_dr     = 0.00;
            $ledger->savings_cr     = 0.00;
            $ledger->savings_bal    = $member->savingsBalance();
            $ledger->long_term_dr   = 0.00;
            $ledger->long_term_cr   = $request->adjustment;
            $ledger->long_term_bal  = ($last_loan_payment->bal - $request->adjustment);
            $ledger->short_term_dr  = 0.00;
            $ledger->short_term_cr  = 0.00;
            $ledger->short_term_bal = $member->shortTermLoanBalance();
            $ledger->commodity_dr   = 0.00;
            $ledger->commodity_cr   = 0.00;
            $ledger->commodity_bal  = $member->commodityLoanBalance();
            $ledger->done_by        = auth()->user()->ippis;
            $ledger->save();

            $activityLog = new ActivityLog;
            $activityLog->logThis($trxnNumber, $member->ippis, 'LTL LOAN ADJUSTMENT', $request->adjustment, 1, auth()->user()->ippis);
        }

        // MAKE ENTRY FOR NEW LOAN BEING TAKEN
        //
        // get last long term loan
        $longLongTermRecord = $member->latest_long_term_loan();

        if(isset($longLongTermRecord)) {
            $longLongTermPaymentRecord = $member->latest_long_term_payment();
            
            $long_term_bal = isset($longLongTermPaymentRecord) ? $longLongTermPaymentRecord->bal + ($request->total_amount - $request->adjustment) : ($request->total_amount - $request->adjustment);

        } else {

            $long_term_bal = ($request->total_amount - $request->adjustment);

        }

        // get loan end date
        $loanEndDate = Carbon::parse($request->loan_date)->addMonths($request->no_of_months);

        // make entry in long term loan table
        $net_payment = $request->total_amount - ($request->adjustment + $request->processing_fee + $request->bank_charges + $request->interest);

        $ltl                      = new LongTerm;
        $ltl->pay_point           = $member->pay_point;
        $ltl->ref                 = $request->ref;
        $ltl->loan_date           = $request->loan_date;
        $ltl->loan_end_date       = $loanEndDate;
        $ltl->ippis               = $ippis;
        $ltl->no_of_months        = $request->no_of_months;
        $ltl->total_amount        = $request->total_amount;
        $ltl->monthly_amount      = ($request->total_amount) / $request->no_of_months;
        $ltl->adjustment          = $request->adjustment;
        $ltl->processing_fee      = $request->processing_fee;
        $ltl->bank_charges        = $request->bank_charges;
        $ltl->interest            = $request->interest;
        $ltl->interest_percentage = $request->interest_percentage;
        $ltl->net_payment         = $net_payment;
        $ltl->guarantor_1         = $request->guarantor_1;
        $ltl->guarantor_2         = $request->guarantor_2;
        $ltl->pv_number           = $ltl->pvNumberGenerator();
        $ltl->done_by             = auth()->user()->ippis;
        $ltl->save();


        // make entry in long term payments table
        $ltlPayment                = new LongTermPayment;
        $ltlPayment->trxn_number    = $trxnNumber;
        $ltlPayment->trxn_type      = 'ltl';
        $ltlPayment->is_authorized = 0;
        $ltlPayment->ippis         = $ippis;
        $ltlPayment->pay_point     = $member->pay_point;
        $ltlPayment->ref           = $request->ref;
        $ltlPayment->loan_date     = $request->loan_date;
        $ltlPayment->long_term_id  = $ltl->id;
        $ltlPayment->dr            = $request->total_amount;
        $ltlPayment->cr            = 0.00;
        $ltlPayment->bal           = $long_term_bal;
        $ltlPayment->month         = Carbon::today()->format('m');
        $ltlPayment->year          = Carbon::today()->format('Y');
        $ltlPayment->done_by       = auth()->user()->ippis;
        $ltlPayment->save();


        // make ledger entry
        $ledger                 = new Ledger;
        $ledger->trxn_number    = $trxnNumber;
        $ledger->trxn_type      = 'ltl';
        $ledger->is_authorized  = 0;
        $ledger->member_id      = $member->id;
        $ledger->pay_point      = $member->pay_point;
        $ledger->date           = $request->loan_date;
        $ledger->ref            = $request->ref;
        $ledger->loan_date      = $request->loan_date;
        $ledger->savings_dr     = 0.00;
        $ledger->savings_cr     = 0.00;
        $ledger->savings_bal    = $member->savingsBalance();
        $ledger->long_term_dr   = $request->total_amount;
        $ledger->long_term_cr   = 0.00;
        $ledger->long_term_bal  = $long_term_bal;
        $ledger->short_term_dr  = 0.00;
        $ledger->short_term_cr  = 0.00;
        $ledger->short_term_bal = $member->shortTermLoanBalance();
        $ledger->commodity_dr   = 0.00;
        $ledger->commodity_cr   = 0.00;
        $ledger->commodity_bal  = $member->commodityLoanBalance();
        $ledger->done_by        = auth()->user()->ippis;
        $ledger->save();

        $member->savings_locked = 1; // lock savings
        $member->save();

        $activityLog = new ActivityLog;
        $activityLog->logThis($trxnNumber, $member->ippis, '(LTL) '.$request->ref, $request->total_amount, 0, auth()->user()->ippis);

        // Toastr::success('Loan successful', 'Success', ["positionClass" => "toast-bottom-right"]);
        // return redirect()->route('members.longTermLoans', $ippis);

        return ['long_term_loan' => $ltl];
    }

    /**
     * Show repayment form
     */
    function longLoanRepayment($ippis) {

        $member = Member::where('ippis', $ippis)->first();
        
        if ($member->is_active == 0) {
            Toastr::error('This member has been deactivated', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.dashboard', $member->ippis);
        }

        // ensure certain bio details are entered
        $ensureMemberDetails = $member->ensureMemberDetails();
        if (!$ensureMemberDetails):            
            flash('TO proceed, you must update the following details: Phone number, Email, Paypoint and Centre')->error();
            // return redirect()->route('editMember', $ippis);
        endif;

        if(!isset($member)) {
            Toastr::error('This member does not exist', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.longTermLoans', $ippis);
        }

        $longTermLoans = LongTerm::pluck('ref', 'id');

        $lastLongTermPayment = $member->latest_long_term_payment();

        $last_long_term_loan_payment = $lastLongTermPayment ? : 0;

        $last_savings = $member->latest_monthly_savings_payment();
        $savings_bal = isset($last_savings) ? $last_savings->bal : 0;

        $repaymentModes = [
            ['key' => 'savings',            'value' => 'From Savings'],
            ['key' => 'direct_deduction',   'value' => 'Direct Deduction'],
            ['key' => 'bank_deposit',       'value' => 'Bank Deposit'],
            ['key' => 'liquidate',          'value' => 'Liquidate'],
        ];


        $longTermLoan_no_of_months = $lastLongTermPayment->longTermLoan->no_of_months; // get loan duration
        if($longTermLoan_no_of_months) {
            $loanDuration = LoanDuration::where(['code' => 'ltl', 'number_of_months' => $longTermLoan_no_of_months])->first();
            $max_deductable_savings_amount = $savings_bal - ($last_long_term_loan_payment->bal / $loanDuration->determinant_factor);
        } else {
            $max_deductable_savings_amount = $savings_bal;
        }

        if (request()->ajax()) {
            return [
                'long_term_loans'       => $longTermLoans, 
                'last_long_term_loan_payment'       => $last_long_term_loan_payment, 
                'member'                => $member, 
                'repayment_modes'       => $repaymentModes,
                'last_long_term_payment'   => $lastLongTermPayment,
                'savings_bal'           => $savings_bal,
                'max_deductable_savings_amount' => $max_deductable_savings_amount,
            ];
        }

        $data['longTermLoans']  = $longTermLoans;
        $data['member']         = $member;
        $data['repaymentModes'] = $repaymentModes;
        $data['savings_bal']    = $savings_bal;

        return view('members.long_term.repayment', $data);
    }

    /**
     * save long term loan repayment
     */
    function postLongLoanRepayment(Request $request, $ippis) {
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
            'ref.required' => 'The ref is required',
            'deposit_date.required' => 'The repayment date is required',
            'ippis.required' => 'This IPPIS Number is required',
            'total_amount.required' => 'Kindly enter the amount to deduct',
            'repayment_mode.required' => 'Kindly select a repayment type',
        ];

        $this->validate($request, $rules, $messages);

        $member = Member::where('ippis', $ippis)->first();

        $longLongTermRecord = $member->latest_long_term_loan();

        if(isset($longLongTermRecord)) {
            
            $longLongTermPaymentRecord = $member->latest_long_term_payment();
            
            $long_term_bal = isset($longLongTermPaymentRecord) ? $longLongTermPaymentRecord->bal - $request->total_amount : 0 - $request->total_amount;

        } else {

            Toastr::error('No Long Term Loan exists', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.longTermLoans', $ippis);

        }

        // generate a code to be used for this ledger entry
        $ledger4trxnNumber = new Ledger;
        $trxnNumber = $ledger4trxnNumber->generateTrxnNumber();

        if($request->repayment_mode == 'savings' || $request->repayment_mode == 'liquidate') {

            $lastMonthlySavingRecord = $member->latest_monthly_saving();

            $lastMonthlySavingsPaymentRecord = $member->latest_monthly_savings_payment(); 
                
            $savings_bal = isset($lastMonthlySavingsPaymentRecord) ? $lastMonthlySavingsPaymentRecord->bal - $request->total_amount : 0 - $request->total_amount;


            // make entry in long term payments table
            $msPayment                    = new MonthlySavingsPayment;
            $msPayment->monthly_saving_id = $lastMonthlySavingRecord ? $lastMonthlySavingRecord->id : 0;
            $msPayment->trxn_number       = $trxnNumber;
            $msPayment->trxn_type         = 'ltl_Rp_Savings';
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

            // make entry in long term payments table
            // $ltlPayment                = new LongTermPayment;
            // $ltlPayment->trxn_number   = $trxnNumber;
            // $ltlPayment->trxn_type     = 'ltl_Rp_Savings';
            // $ltlPayment->is_authorized = 1;
            // $ltlPayment->ippis         = $ippis;
            // $ltlPayment->pay_point     = $member->pay_point;
            // $ltlPayment->ref           = $request->ref;
            // $ltlPayment->deposit_date  = $request->deposit_date;
            // $ltlPayment->long_term_id  = $longLongTermRecord->id;
            // $ltlPayment->dr            = 0.00;
            // $ltlPayment->cr            = $request->total_amount;
            // $ltlPayment->bal           = $long_term_bal;
            // $ltlPayment->month         = Carbon::today()->format('m');
            // $ltlPayment->year          = Carbon::today()->format('Y');
            // $ltlPayment->done_by       = auth()->user()->ippis;
            // $ltlPayment->save();


            // make ledger entry
            $ledger                = new Ledger;
            $ledger->trxn_number   = $trxnNumber;
            $ledger->trxn_type     = 'ltl_Rp_Savings';
            $ledger->is_authorized = 1;
            $ledger->member_id     = $member->id;
            $ledger->pay_point     = $member->pay_point;
            $ledger->date          = Carbon::today()->format('Y-m-d');
            $ledger->ref           = $request->ref;
            $ledger->withdrawal_date  = $request->deposit_date;
            $ledger->savings_dr    = $request->total_amount;
            $ledger->savings_cr    = 0.00;
            $ledger->savings_bal   = $savings_bal;
            $ledger->done_by       = auth()->user()->ippis;
            $ledger->save();

            // fire event to save trxn in ledger transactions
            $ledgerInternal = new Ledger_Internal;
            $ledgerInternal->recordLTLRepaymentViaSavings($member, $msPayment->dr, $member->ippis.' LTL Repay. Sav.', $request->deposit_date);

            $activityLog = new ActivityLog;
            $activityLog->logThis($trxnNumber, $member->ippis, '(LTL Repayment from Savings) '.$request->ref, $request->total_amount, 1, auth()->user()->ippis);
        }


        if($request->repayment_mode == 'savings' || $request->repayment_mode == 'liquidate') {
            $trxn_type = 'ltl_Rp_Savings';
            $is_authorized = 1;
        } else {
            $trxn_type = 'ltl_Rp_Deposit';
            $is_authorized = 0;
        }

        // make entry in long term payments table
        $ltlPayment                = new LongTermPayment;
        $ltlPayment->trxn_number   = $trxnNumber;
        $ltlPayment->trxn_type     = $trxn_type;
        $ltlPayment->is_authorized = $is_authorized;
        $ltlPayment->ippis         = $ippis;
        $ltlPayment->pay_point     = $member->pay_point;
        $ltlPayment->ref           = $request->ref;
        $ltlPayment->deposit_date  = $request->deposit_date;
        $ltlPayment->long_term_id  = $longLongTermRecord->id;
        $ltlPayment->dr            = 0.00;
        $ltlPayment->cr            = $request->total_amount;
        $ltlPayment->bal           = $long_term_bal;
        $ltlPayment->month         = Carbon::today()->format('m');
        $ltlPayment->year          = Carbon::today()->format('Y');
        $ltlPayment->done_by       = auth()->user()->ippis;
        $ltlPayment->save();


        // make ledger entry
        $ledger = new Ledger;
        $ledger->trxn_number    = $trxnNumber;
        $ledger->trxn_type      = $trxn_type;
        $ledger->is_authorized  = $is_authorized;
        $ledger->member_id      = $member->id;
        $ledger->pay_point      = $member->pay_point;
        $ledger->date           = Carbon::today()->format('Y-m-d');
        $ledger->ref            = $request->ref;
        $ledger->deposit_date   = $request->deposit_date;
        $ledger->savings_dr     = 0.00;
        $ledger->savings_cr     = 0.00;
        $ledger->savings_bal    = $member->savingsBalance();
        $ledger->long_term_dr   = 0.00;
        $ledger->long_term_cr   = $request->total_amount;
        $ledger->long_term_bal  = $long_term_bal;
        $ledger->short_term_dr  = 0.00;
        $ledger->short_term_cr  = 0.00;
        $ledger->short_term_bal = $member->shortTermLoanBalance();
        $ledger->commodity_dr   = 0.00;
        $ledger->commodity_cr   = 0.00;
        $ledger->commodity_bal  = $member->commodityLoanBalance();
        $ledger->done_by        = auth()->user()->ippis;
        $ledger->save();

        if($request->repayment_mode == 'savings' || $request->repayment_mode == 'liquidate') {
            $member->savings_locked = 0; // unlock savings
            $member->save();
        }

        $activityLog = new ActivityLog;
        $activityLog->logThis($trxnNumber, $member->ippis, '(LTL) '.$request->ref, $request->total_amount, $request->repayment_mode == 'savings' ? 1 : 0, auth()->user()->ippis);

        // dd($ltl, $ledger);
        Toastr::success('Repayent successful', 'Success', ["positionClass" => "toast-bottom-right"]);

        return redirect()->route('members.longTermLoans', $ippis);

    }

    /**
     * View loan details
     */
    function loanDetails($loanID) {
        $data['loan'] = LongTerm::find($loanID);

        return view('members.long_term.loan_details', $data);
    }

    /**
     * Generate payment voucher for loan
     */
    function longTermLoansPaymentVoucher($loanId) {
        $data['currency'] = new CurrencyInWords();
        $data['loan'] = LongTerm::where('id', $loanId)->with('member.member_pay_point')->first();

        return view('members.long_term.pv', $data);
    }

    /**
     * Generate payment voucher for loan in PDF
     */
    function longTermLoansPaymentVoucherPDF($loanId) {
        $data['currency'] = new CurrencyInWords();
        $data['loan'] = LongTerm::where('id', $loanId)->with('member.member_pay_point')->first();
        $pdf = \PDF::loadView('pdf.pv', $data)->setPaper('a4', 'portrait');

        return $pdf->download('PV_'.$data['loan']->member->full_name.'.pdf');
    }

}
