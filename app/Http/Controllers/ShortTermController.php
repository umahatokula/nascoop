<?php

namespace App\Http\Controllers;

use App\Member;
use App\Bank;
use App\ShortTerm;
use App\ShortTermPayment;
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

class ShortTermController extends Controller
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
     * Short term loans
     */
    function shortTermLoans(Request $request, $ippis) {

        $date_from = Carbon::now()->startOfYear();
        $date_to = Carbon::now()->endOfYear();
        $data['member'] = Member::where('ippis', $ippis)->first();

        if(!isset($data['member'])) {
            Toastr::error('This member does not exist', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.longTermLoans', $ippis);
        }

        $shortTermLoans = ShortTerm::query();
        $shortTermLoans = $shortTermLoans->with('payments')->where('ippis', $ippis);


        if ($request->date_from) {    
            $date_from = $request->date_from;
            $date_to = $request->date_to; 
        }
            
        $shortTermLoans = $shortTermLoans->whereBetween('loan_date', [$date_from, $date_to]);

        $data['date_from'] = $date_from;
        $data['date_to'] = $date_to;
        $data['shortTermLoans'] = $shortTermLoans->get();

        return view('members.short_term.short_term', $data);
    }

    /**
     * Short term loans
     */
    function newShortLoan($ippis) {

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

        if(!isset($data['member'])) {
            Toastr::error('This member does not exist', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.longTermLoans', $ippis);
        }
        
        if(request()->ajax()) {
            $lastSavingsPayment = $member->latest_monthly_savings_payment();
            $lastLoanPayment = $member->latest_short_term_payment();
            $banks = Bank::all();
            $processingFee = ProcessingFee::first();
            // $periods = [
            //     ['duration' => '3',  'label' => "3 Months", 'interest' => 4],
            //     ['duration' => '4',  'label' => "4 Months", 'interest' => 6],
            //     ['duration' => '5',  'label' => "5 Months", 'interest' => 8],
            // ];
            $periods = LoanDuration::where('code', 'stl')->get();

            return [
                'banks'         => $banks,
                'savings'       => $lastSavingsPayment,
                'periods'       => $periods,
                'lastLoan'      => $lastLoanPayment,
                'member'        => $member,
                'processingFee' => $processingFee,
                'maxLoanAmount' => 500000,
            ];
        } 

        return view('members.short_term.new_short_term_loan', $data);
    }

    /**
     * Save new short term loan
     */
    function postNewShortLoan(Request $request, $ippis) {
        // dd($request->all(), $ippis);

        $rules = [
            'ref' => 'required',
            'loan_date' => 'required',
            'no_of_months' => 'required',
            'ippis' => 'required',
            'total_amount' => 'numeric|required',
            'bank_charges' => 'required',
            // 'guarantor_1' => 'required',
            // 'guarantor_2' => 'required',
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

        // MAKE ENTRY FOR LOAN ADJUSTMENT (I.E LOAN REPAYMENT)
        if ($request->adjustment > 0) {

            $last_loan = $member->latest_short_term_loan();
            $last_loan_payment = $member->latest_short_term_payment();

            // make entry in short term payments table for loan adjustment
            $stlPayment                = new ShortTermPayment;
            $stlPayment->trxn_number   = $trxnNumber;
            $stlPayment->trxn_type     = 'stl_Rp_Savings';
            $stlPayment->is_authorized = 3;
            $stlPayment->ippis         = $ippis;
            $stlPayment->pay_point     = $member->pay_point;
            $stlPayment->ref           = 'LOAN ADJUSTMENT';
            $stlPayment->loan_date     = $request->loan_date;
            $stlPayment->short_term_id = $last_loan->id;
            $stlPayment->dr            = 0.00;
            $stlPayment->cr            = $request->adjustment;
            $stlPayment->bal           = ($last_loan_payment->bal - $request->adjustment);
            $stlPayment->month         = Carbon::today()->format('m');
            $stlPayment->year          = Carbon::today()->format('Y');
            $stlPayment->done_by       = auth()->user()->ippis;
            $stlPayment->save();

            // make ledger entry for loan adjustment
            $ledger                 = new Ledger;
            $ledger->trxn_number    = $trxnNumber;
            $ledger->trxn_type      = 'stl_Rp_Savings';
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
            $ledger->long_term_cr   = 0.00;
            $ledger->long_term_bal  = $member->longTermLoanBalance();
            $ledger->short_term_dr  = 0.00;
            $ledger->short_term_cr  = $request->adjustment;
            $ledger->short_term_bal = ($last_loan_payment->bal - $request->adjustment);
            $ledger->commodity_dr   = 0.00;
            $ledger->commodity_cr   = 0.00;
            $ledger->commodity_bal  = $member->commodityLoanBalance();
            $ledger->done_by        = auth()->user()->ippis;
            $ledger->save();

            // fire event to save trxn in ledger transactions
            // $ledgerInternal = new Ledger_Internal;
            // $ledgerInternal->recordSTLRepaymentViaSavings($member, $stlPayment->cr, $member->ippis.' stl_Rp_Savings');

            $activityLog = new ActivityLog;
            $activityLog->logThis($trxnNumber, $member->ippis, 'LOAN ADJUSTMENT', $request->adjustment, 1, auth()->user()->ippis);
        }
            
        $latestShortTermPaymentRecord = $member->latest_short_term_payment();

        if(isset($latestShortTermPaymentRecord)) {
            
            $short_term_bal = isset($latestShortTermPaymentRecord) ? $latestShortTermPaymentRecord->bal + ($request->total_amount - $request->adjustment) : ($request->total_amount - $request->adjustment);

        } else {

            $short_term_bal = $request->total_amount - $request->adjustment;

        }

        // MAKE ENTRY FOR NEW LOAN BEING TAKEN
        //
        // get loan end date
        $loanEndDate = Carbon::parse($request->loan_date)->addMonths($request->no_of_months);

        // make entry in short term loan table
        $net_payment = $request->total_amount - ($request->adjustment + $request->processing_fee + $request->bank_charges + $request->interest);
        
        $stl                      = new ShortTerm;
        $stl->pay_point           = $member->pay_point;
        $stl->ref                 = $request->ref;
        $stl->loan_date           = $request->loan_date;
        $stl->loan_end_date       = $loanEndDate;
        $stl->ippis               = $ippis;
        $stl->no_of_months        = $request->no_of_months;
        $stl->total_amount        = $request->total_amount;
        $stl->monthly_amount      = ($request->total_amount) / $request->no_of_months;
        $stl->adjustment          = $request->adjustment;
        $stl->processing_fee      = $request->processing_fee;
        $stl->bank_charges        = $request->bank_charges;
        $stl->interest            = $request->interest;
        $stl->interest_percentage = $request->interest_percentage;
        $stl->net_payment         = $net_payment;
        $stl->guarantor_1         = $request->guarantor_1;
        $stl->guarantor_2         = $request->guarantor_2;
        $stl->pv_number           = $stl->pvNumberGenerator();
        $stl->done_by             = auth()->user()->ippis;
        $stl->save();

        // generate a code to be used for this ledger entry
        // $ledger = new Ledger;
        // $trxnNumber = $ledger->generateTrxnNumber();

        // make entry in short term payments table
        $stlPayment                = new ShortTermPayment;
        $stlPayment->trxn_number   = $trxnNumber;
        $stlPayment->trxn_type     = 'stl';
        $stlPayment->is_authorized = 0;
        $stlPayment->ippis         = $ippis;
        $stlPayment->pay_point     = $member->pay_point;
        $stlPayment->ref           = $request->ref;
        $stlPayment->loan_date     = $request->loan_date;
        $stlPayment->short_term_id = $stl->id;
        $stlPayment->dr            = $request->total_amount;
        $stlPayment->cr            = 0.00;
        $stlPayment->bal           = $short_term_bal;
        $stlPayment->month         = Carbon::today()->format('m');
        $stlPayment->year          = Carbon::today()->format('Y');
        $stlPayment->done_by       = auth()->user()->ippis;
        $stlPayment->save();


        // make ledger entry
        $ledger                 = new Ledger;
        $ledger->trxn_number    = $trxnNumber;
        $ledger->trxn_type      = 'stl';
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
        $ledger->short_term_dr  = $request->total_amount;
        $ledger->short_term_cr  = 0.00;
        $ledger->short_term_bal = $short_term_bal;
        $ledger->commodity_dr   = 0.00;
        $ledger->commodity_cr   = 0.00;
        $ledger->commodity_bal  = $member->commodityLoanBalance();
        $ledger->done_by        = auth()->user()->ippis;
        $ledger->save();

        $member->savings_locked = 1; // lock savings
        $member->save();

        $activityLog = new ActivityLog;
        $activityLog->logThis($trxnNumber, $member->ippis, '(STL) '.$request->ref, $request->total_amount, 0, auth()->user()->ippis);

        // Toastr::success('Loan successful', 'Success', ["positionClass" => "toast-bottom-right"]);
        // return redirect()->route('members.shortTermLoans', $ippis);

        return ['short_term_loan' => $stl];
    }

    /**
     * Show reayment form
     */
    function shortLoanRepayment($ippis) {
        
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

        $shortTermLoans = ShortTerm::pluck('ref', 'id');
        $lastLongTermPayment = $member->latest_long_term_payment();
        $last_long_term_loan_payment = $lastLongTermPayment ? : 0;

        $lastShortTermPayment = $member->latest_short_term_payment();

        $last_short_term_loan_payment = $lastShortTermPayment ? : 0;

        $last_savings = $member->latest_monthly_savings_payment();
        $savings_bal = isset($last_savings) ? $last_savings->bal : 0;

        $repaymentModes = [
            ['key' => 'savings', 'value' => 'From savings'],
            ['key' => 'direct_deduction', 'value' => 'Direct deduction'],
            ['key' => 'bank_deposit', 'value' => 'Bank deposit'],
        ];

        
        $longTermLoan_no_of_months = !$lastLongTermPayment ? null : ($lastLongTermPayment->longTermLoan ? $lastLongTermPayment->longTermLoan->no_of_months : null); // get loan duration
        if($longTermLoan_no_of_months) {
            $loanDuration = LoanDuration::where(['code' => 'ltl', 'number_of_months' => $longTermLoan_no_of_months])->first();
            $max_deductable_savings_amount = $savings_bal - ($last_long_term_loan_payment->bal / $loanDuration->determinant_factor);
        } else {
            $max_deductable_savings_amount = $savings_bal;
        }

        if (request()->ajax()) {
            return [
                'short_term_loans'              => $shortTermLoans,
                'last_short_term_loan_payment'  => $last_short_term_loan_payment,
                'member'                        => $member,
                'repayment_modes'               => $repaymentModes,
                'last_long_term_payment'        => $lastLongTermPayment,
                'savings_bal'                   => $savings_bal,
                'max_deductable_savings_amount' => $max_deductable_savings_amount,
            ];
        }

        $data['shortTermLoans'] = $shortTermLoans;
        $data['member']         = $member;
        $data['repaymentModes'] = $repaymentModes;

        return view('members.short_term.repayment', $data);
    }

    /**
     * Show reayment form
     */
    function postShortLoanRepayment(Request $request, $ippis) {
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
            'total_amount.required' => 'Kindly enter the amount to deduct',
            'repayment_mode.required' => 'Kindly select a repayment type',
        ];

        $this->validate($request, $rules, $messages);

        $member = Member::where('ippis', $ippis)->first();

        $shortShortTermRecord = $member->latest_short_term_loan();

        if(isset($shortShortTermRecord)) {
            
            $shortShortTermPaymentRecord = $member->latest_short_term_payment();
            
            $short_term_bal = isset($shortShortTermPaymentRecord) ? $shortShortTermPaymentRecord->bal - $request->total_amount : 0 - $request->total_amount;

        } else {

            Toastr::error('No Short Term Loan exists', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.shortTermLoans', $ippis);

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
            $msPayment->trxn_type         = 'stl_Rp_Savings';
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
            // $stlPayment                = new ShortTermPayment;
            // $stlPayment->trxn_number   = $trxnNumber;
            // $stlPayment->trxn_type     = 'stl_Rp_Savings';
            // $stlPayment->is_authorized = $request->repayment_mode == 'savings' ? 1 : 0;
            // $stlPayment->ippis         = $ippis;
            // $stlPayment->pay_point     = $member->pay_point;
            // $stlPayment->ref           = $request->ref;
            // $stlPayment->deposit_date  = $request->deposit_date;
            // $stlPayment->short_term_id = $shortShortTermRecord->id;
            // $stlPayment->dr            = 0.00;
            // $stlPayment->cr            = $request->total_amount;
            // $stlPayment->bal           = $short_term_bal;
            // $stlPayment->month         = Carbon::today()->format('m');
            // $stlPayment->year          = Carbon::today()->format('Y');
            // $stlPayment->done_by       = auth()->user()->ippis;
            // $stlPayment->save();


            // make ledger entry
            $ledgerF                = new Ledger;
            $ledgerF->trxn_number   = $trxnNumber;
            $ledgerF->trxn_type     = 'stl_Rp_Savings';
            $ledgerF->is_authorized = 1;
            $ledgerF->member_id     = $member->id;
            $ledgerF->pay_point     = $member->pay_point;
            $ledgerF->date          = Carbon::today()->format('Y-m-d');
            $ledgerF->ref           = $request->ref;
            $ledgerF->withdrawal_date  = $request->deposit_date;
            $ledgerF->savings_dr    = $request->total_amount;
            $ledgerF->savings_cr    = 0.00;
            $ledgerF->savings_bal   = $savings_bal;
            $ledgerF->done_by       = auth()->user()->ippis;
            $ledgerF->save();
            // dd($ledger);


            // fire event to save trxn in ledger transactions
            $ledgerInternal = new Ledger_Internal;
            $ledgerInternal->recordSTLRepaymentViaSavings($member, $msPayment->dr, $member->ippis.' STL Repay. Sav.', $request->deposit_date);

            $activityLog = new ActivityLog;
            $activityLog->logThis($trxnNumber, $member->ippis, '(STL Repayment from Savings) '.$request->ref, $request->total_amount, 1, auth()->user()->ippis);
        }

        // make entry in short term payments table
        $stlPayment                = new ShortTermPayment;
        $stlPayment->trxn_number   = $trxnNumber;
        $stlPayment->trxn_type     = $request->repayment_mode == 'savings' ? 'stl_Rp_Savings' : 'stl_Rp_Deposit';
        $stlPayment->is_authorized = $request->repayment_mode == 'savings' ? 1 : 0;
        $stlPayment->ippis         = $ippis;
        $stlPayment->pay_point     = $member->pay_point;
        $stlPayment->ref           = $request->ref;
        $stlPayment->deposit_date  = $request->deposit_date;
        $stlPayment->short_term_id = $shortShortTermRecord->id;
        $stlPayment->dr            = 0.00;
        $stlPayment->cr            = $request->total_amount;
        $stlPayment->bal           = $short_term_bal;
        $stlPayment->month         = Carbon::today()->format('m');
        $stlPayment->year          = Carbon::today()->format('Y');
        $stlPayment->done_by       = auth()->user()->ippis;
        $stlPayment->save();


        // make ledger entry
        $ledger                 = new Ledger;
        $ledger->trxn_number    = $trxnNumber;
        $ledger->trxn_type  = $request->repayment_mode == 'savings' ? 'stl_Rp_Savings' : 'stl_Rp_Deposit';
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
        $ledger->short_term_cr  = $request->total_amount;
        $ledger->short_term_bal = $short_term_bal;
        $ledger->commodity_dr   = 0.00;
        $ledger->commodity_cr   = 0.00;
        $ledger->commodity_bal  = $member->commodityLoanBalance();
        $ledger->done_by        = auth()->user()->ippis;
        $ledger->save();

        $member->savings_locked = $request->repayment_mode == 'savings' ? 0 : 1; // lock savings
        $member->save();

        $activityLog = new ActivityLog;
        $activityLog->logThis($trxnNumber, $member->ippis, '(STL) '.$request->ref, $request->total_amount, $request->repayment_mode == 'savings' ? 1 : 0, auth()->user()->ippis);

        // dd($stl, $ledger);

        Toastr::success('Repayent successful', 'Success', ["positionClass" => "toast-bottom-right"]);
        return redirect()->route('members.shortTermLoans', $ippis);

    }


    function loanDetails($loanID) {
        $data['loan'] = ShortTerm::find($loanID);

        return view('members.short_term.loan_details', $data);
    }

    /**
     * Generate payment voucher for loan
     */
    function shortTermLoansPaymentVoucher($loanId) {
        $data['currency'] = new CurrencyInWords();
        $data['loan'] = ShortTerm::where('id', $loanId)->with('member.member_pay_point')->first();

        return view('members.short_term.pv', $data);
    }

    /**
     * Generate payment voucher for loan in PDF
     */
    function shortTermLoansPaymentVoucherPDF($loanId) {
        $data['currency'] = new CurrencyInWords();
        $data['loan'] = ShortTerm::where('id', $loanId)->with('member.member_pay_point')->first();
        $pdf = \PDF::loadView('pdf.pv', $data)->setPaper('a4', 'portrait');

        return $pdf->download('PV_'.$data['loan']->member->full_name.'.pdf');
    }

}
