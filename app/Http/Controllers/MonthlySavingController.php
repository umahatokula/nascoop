<?php

namespace App\Http\Controllers;

use App\Member;
use App\Bank;
use App\MonthlySaving;
use App\MonthlySavingsPayment;
use App\Ledger;
use App\LongTerm;
use App\LongTermPayment;
use Carbon\Carbon;
use App\ActivityLog;
use App\Helpers\CurrencyInWords;
use App\ProcessingFee;
use App\PVNumber;
use App\LoanDuration;
use Toastr;

use Illuminate\Http\Request;

class MonthlySavingController extends Controller
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
     * Long term loans
     */
    function monthlySavings($ippis) {

        $data['member'] = Member::where('ippis', $ippis)->first();

        if(!isset($data['member'])) {
            Toastr::error('This member does not exist', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.longTermLoans', $ippis);
        }

        $data['monthlySavings'] = MonthlySaving::with('payments')->where('ippis', $ippis)->get();
        // dd($data['monthlySavings']);

        return view('members.monthly_savings.monthly_savings', $data);
    }

    /**
     * Long term loans
     */
    function newSavings($ippis) {

        $member = Member::where('ippis', $ippis)->first(); 

        $data['member'] = $member;

        if(!isset($data['member'])) {
            Toastr::error('This member does not exist', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.longTermLoans', $ippis);
        }

        // ensure certain bio details are entered
        $ensureMemberDetails = $member->ensureMemberDetails();
        if (!$ensureMemberDetails):            
            flash('TO proceed, you must update the following details: Phone number, Email, Paypoint and Centre')->error();
            // return redirect()->route('editMember', $ippis);
        endif;
        
        if ($member->is_active == 0) {
            Toastr::error('This member has been deactivated', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->back();
        }

        return view('members.monthly_savings.new_monthly_savings', $data);
    }

    /**
     * Save new savings
     */
    function postNewSavings(Request $request, $ippis) {
        // dd($request->all(), $ippis);

        $rules = [
            'ippis' => 'required',
            'total_amount' => 'numeric|required',
            'deposit_date' => 'required|date_format:Y-m-d',
            // 'email' => 'email|unique:member,email',

        ];

        $messages = [
            'deposit_date.required' => 'The date is required',
            'deposit_date.date_format' => 'The date format must be MM/DD/YYYY',
            'total_amount.required' => 'The amount is required',
            'ippis.required' => 'This IPPIS Number is required',
        ];

        $this->validate($request, $rules, $messages);

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
            
            $savings_bal = isset($lastMonthlySavingsPaymentRecord) ? $lastMonthlySavingsPaymentRecord->bal + $request->total_amount : $request->total_amount;

        } else {

            $savings_bal = $request->total_amount;

        }

        // generate a code to be used for this ledger entry
        $ledger = new Ledger;
        $trxnNumber = $ledger->generateTrxnNumber();

        
        // make entry in monthly savings payments table
        $msPayment                    = new MonthlySavingsPayment;
        $msPayment->trxn_number       = $trxnNumber;
        $msPayment->trxn_type         = 'dp_S';
        $msPayment->is_authorized     = 0;
        $msPayment->monthly_saving_id = $lastMonthlySavingRecord->id;
        $msPayment->ippis             = $request->ippis;
        $msPayment->pay_point         = $member->pay_point;
        $msPayment->deposit_date      = $request->deposit_date;
        $msPayment->ref               = $request->ref;
        $msPayment->dr                = 0.00;
        $msPayment->cr                = $request->total_amount;
        $msPayment->bal               = $savings_bal;
        $msPayment->month             = Carbon::today()->format('m');
        $msPayment->year              = Carbon::today()->format('Y');
        $msPayment->done_by           = auth()->user()->ippis;
        $msPayment->save();


        // make ledger entry
        $ledger                 = new Ledger;
        $ledger->trxn_number    = $trxnNumber;
        $ledger->trxn_type      = 'dp_S';
        $ledger->is_authorized  = 0;
        $ledger->member_id      = $member->id;
        $ledger->pay_point      = $member->pay_point;
        $ledger->date           = $request->deposit_date;
        $ledger->ref            = $request->ref;
        $ledger->savings_dr     = 0.00;
        $ledger->savings_cr     = $request->total_amount;
        $ledger->savings_bal    = $savings_bal;
        $ledger->long_term_dr   = 0.00;
        $ledger->long_term_cr   = 0.00;
        $ledger->long_term_bal  = $member->longTermLoanBalance();
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
        $activityLog->logThis($trxnNumber, $member->ippis, '(Added Savings for) '.$member->ippis.' '.$member->full_name, $request->total_amount, 0, auth()->user()->ippis);

        // dd($ltl, $ledger);

        Toastr::success('Deposit successful', 'Success', ["positionClass" => "toast-bottom-right"]);
        return redirect()->route('members.savings', $ippis);
    }



    /**
     * Withdrawal
     */
    function savingsWithrawal($ippis) {

        $member = Member::where('ippis', $ippis)->first(); 

        if(!isset($member)) {
            Toastr::error('This member does not exist', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.longTermLoans', $ippis);
        }

        // ensure certain bio details are entered
        $ensureMemberDetails = $member->ensureMemberDetails();
        if (!$ensureMemberDetails):            
            flash('TO proceed, you must update the following details: Phone number, Email, Paypoint and Centre')->error();
            // return redirect()->route('editMember', $ippis);
        endif;
        
        if ($member->is_active == 0) {
            Toastr::error('This member has been deactivated', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->back();
        }

        $lastLongTermPayment = $member->latest_long_term_payment();
        $lastMonthlySaving = $member->latest_monthly_savings_payment();
        $banks = Bank::all();
        $processingFee = ProcessingFee::first();

        if ($member->latest_long_term_loan()) {
            $no_of_months = $member->latest_long_term_loan()->no_of_months;

            if ($no_of_months != 0) {
                $period = LoanDuration::where(['code' => 'ltl', 'number_of_months' => $no_of_months])->first();
            } else {
                $period = null;
            }
            
        } else {
            $period = null;
        }
        // dd($period);

        if (request()->ajax()) {
            return [
                'member'                    => $member, 
                'last_long_term_payment'    => $lastLongTermPayment ? $lastLongTermPayment->load('longTermLoan') : $lastLongTermPayment = ['bal' => 0],
                'last_monthly_saving'       => $lastMonthlySaving,
                'interest_percentage'       => 2.5,
                'banks'                     => $banks, 
                'processingFee'             => $processingFee,
                'period'                    => $period,
            ];
        }

        $data['member'] = $member;
        $data['lastMonthlySaving'] = $lastMonthlySaving;

        return view('members.monthly_savings.withdrawal', $data);
    }

    /**
     * Post withdrawal
     */
    function postSavingsWithrawal(Request $request, $ippis) {
        // dd($request->all(), $ippis);

        $rules = [
            'ippis' => 'required',
            'withdrawal_date' => 'required',
            'total_amount' => 'required|numeric',
            'is_withdrawal' => 'required',
            // 'email' => 'email|unique:member,email',

        ];

        $messages = [
            'total_amount.required' => 'The amount to withdraw is required',
            'withdrawal_date.required' => 'The withdrawal date is required',
            'ippis.required' => 'This IPPIS Number is required',
            'is_withdrawal.required' => 'Please select Withrawal or Refund',
        ];

        $this->validate($request, $rules, $messages);

        $member = Member::where('ippis', $ippis)->first();
        
        if ($member->is_active == 0) {
            Toastr::error('This member has been deactivated', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.dashboard', $member->ippis);
        }

        $lastMonthlySavingRecord = $member->latest_monthly_saving();

        if(isset($lastMonthlySavingRecord)) {
            $lastMonthlySavingsPaymentRecord = $member->latest_monthly_savings_payment();
            
            $savings_bal = isset($lastMonthlySavingsPaymentRecord) ? $lastMonthlySavingsPaymentRecord->bal - $request->total_amount : 0 - $request->total_amount;

        } else {

            Toastr::error('It seems there is no money in this account', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.savings', $ippis);

        }

        // generate a code to be used for this ledger entry
        $ledger = new Ledger;
        $trxnNumber = $ledger->generateTrxnNumber();

        // cal net pay

        // if withdrawal type is WITHDRAWAL, do this
        if ($request->is_withdrawal == 1) {
            $processing_fee = $request->processing_fee;
            $bank_charges = $request->bank_charges;

            // If we are to perform the check of 12 months to apply fee or not, do this
            // if ($request->perform_12_months_check) {
            //     // If the last withdrawal is over 12mnths, the interest of 2.5percent is waived
            //     $lastWithdrawal = MonthlySavingsPayment::where('ippis', $ippis)->where('is_authorized', 1)->where('is_withdrawal', 1)->latest('id')->first();
            //     if($lastWithdrawal) {

            //         $currentWithdrawalDate = Carbon::parse($request->withdrawal_date);
            //         $lastWithdrawalDate = $lastWithdrawal->withdrawal_date;

            //         if($currentWithdrawalDate->diffInMonths($lastWithdrawalDate) >= 12) {
            //             $interest = 0;
            //             $interest_percentage = 0;
            //             $net_payment = $request->total_amount - ($request->processing_fee + $request->bank_charges);
            //         } else {
            //             $interest = $request->interest;
            //             $interest_percentage = $request->interest_percentage;
            //             $net_payment = $request->total_amount - ($request->processing_fee + $request->bank_charges + $request->interest);
            //         }

            //     } else {
            //         $interest = 0;
            //         $interest_percentage = 0;
            //         $net_payment = $request->total_amount - ($request->processing_fee + $request->bank_charges);
            //     }
            // } else {
            //     $processing_fee = $request->processing_fee;
            //     $bank_charges = $request->bank_charges;
            //     $net_payment = $request->total_amount - ($request->processing_fee + $request->bank_charges);
            // }

            // apply withdrawal fee or not
            if ($request->apply_fee) {
                $interest = $request->interest;
                $interest_percentage = $request->interest_percentage;
                $processing_fee = $request->processing_fee;
                $bank_charges = $request->bank_charges;
                $net_payment = $request->total_amount - ($request->processing_fee + $request->bank_charges + $request->interest);
            } else {
                $interest = 0;
                $interest_percentage = 0;
                $processing_fee = $request->processing_fee;
                $bank_charges = $request->bank_charges;
                $net_payment = $request->total_amount - ($request->processing_fee + $request->bank_charges);
            }

        }
        // dd($processing_fee, $bank_charges, $net_payment);

        // if withdrawal type is REFUND, do this
        if ($request->is_withdrawal == 2) {
            $net_payment = $request->total_amount;
            $processing_fee = 0;
            $bank_charges = 0;
            $interest = 0;
            $interest_percentage = 0;
        }

        // make entry in long term payments table
        $msPayment                      = new MonthlySavingsPayment;
        $msPayment->trxn_number         = $trxnNumber;
        $msPayment->trxn_type           = 'wd_S';
        $msPayment->is_authorized       = 0;
        $msPayment->monthly_saving_id   = $lastMonthlySavingRecord->id;
        $msPayment->ippis               = $request->ippis;
        $msPayment->pay_point           = $member->pay_point;
        $msPayment->ref                 = $request->ref;
        $msPayment->deposit_date        = $request->withdrawal_date;
        $msPayment->withdrawal_date     = $request->withdrawal_date;
        $msPayment->dr                  = $request->total_amount;
        $msPayment->cr                  = 0.00;
        $msPayment->bal                 = $savings_bal;
        $msPayment->month               = Carbon::today()->format('m');
        $msPayment->year                = Carbon::today()->format('Y');
        $msPayment->is_withdrawal       = $request->is_withdrawal;
        $msPayment->processing_fee      = $processing_fee;
        $msPayment->bank_charges        = $bank_charges;
        $msPayment->interest            = $interest;
        $msPayment->interest_percentage = $interest_percentage;
        $msPayment->net_payment         = $net_payment;
        $msPayment->pv_number           = $msPayment->pvNumberGenerator();
        $msPayment->done_by             = auth()->user()->ippis;
        $msPayment->save();


        // make ledger entry
        $ledger                  = new Ledger;
        $ledger->trxn_number     = $trxnNumber;
        $ledger->trxn_type       = 'wd_S';
        $ledger->is_authorized   = 0;
        $ledger->member_id       = $member->id;
        $ledger->pay_point       = $member->pay_point;
        $ledger->date            = Carbon::today()->format('Y-m-d');
        $ledger->ref             = $request->ref;
        $ledger->withdrawal_date = $request->withdrawal_date;
        $ledger->savings_dr      = $request->total_amount;
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

        $member->savings_locked = 1; // lock savings
        $member->save();


        $activityLog = new ActivityLog;
        $activityLog->logThis($trxnNumber, $member->ippis, '(Withdrawn Savings for) '.$member->ippis.' '.$member->full_name, $request->total_amount, 0, auth()->user()->ippis);

        // Toastr::success('Withdrawal successful', 'Success', ["positionClass" => "toast-bottom-right"]);
        // return redirect()->route('members.savings', $ippis);

        return ['withdrawal' => $msPayment];
    }

    /**
     * Generate withdrawal voucher
     */
    function withdrawalPaymentVoucher($withrawalId) {
        $data['currency'] = new CurrencyInWords();
        $data['withdrawal'] = MonthlySavingsPayment::where('id', $withrawalId)->with('member.member_pay_point')->first();

        return view('members.monthly_savings.pv', $data);
    }

    /**
     * Generate withdrawal payment voucher in PDF
     */
    function withdrawalPaymentVoucherPDF($withrawalId) {
        $data['currency'] = new CurrencyInWords();
        $data['withdrawal'] = MonthlySavingsPayment::where('id', $withrawalId)->with('member.member_pay_point')->first();
        $pdf = \PDF::loadView('pdf.withdrawal_pv', $data)->setPaper('a4', 'portrait');

        return $pdf->download('PV_'.$data['withdrawal']->member->full_name.'.pdf');
    }


    /**
     * Long term loans
     */
    function savingsChangeObligation($ippis) {
        $member = Member::where('ippis', $ippis)->first();

        if(!isset($member)) {
            Toastr::error('This member does not exist', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.longTermLoans', $ippis);
        }
        
        if ($member->is_active == 0) {
            Toastr::error('This member has been deactivated', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->back();
        }

        $data['member'] = $member;

        return view('members.monthly_savings.change_obligation', $data);
    }

    /**
     * Post withdrawal
     */
    function postSavingsChangeObligation(Request $request, $ippis) {
        // dd($request->all(), $ippis);

        $rules = [
            'ippis' => 'required',
            'old_amount' => 'numeric|required',
            'new_amount' => 'numeric|required',
            'revert_date' => 'required_without:is_indefinite',

        ];

        $messages = [
            'old_amount.required' => 'The old savings amount is required',
            'new_amount.required' => 'The new savings amount is required',
            'ippis.required' => 'This IPPIS Number is required',
            'revert_date.required_without' => 'This revert date is required if change is not indefinite',
        ];

        $this->validate($request, $rules, $messages);

        $member = Member::where('ippis', $ippis)->first();
        
        if ($member->is_active == 0) {
            Toastr::error('This member has been deactivated', 'Error', ["positionClass" => "toast-bottom-right"]);
            return redirect()->route('members.dashboard', $member->ippis);
        }

        $monthlySaving                  = new MonthlySaving;
        $monthlySaving->ippis           = $request->ippis;
        $monthlySaving->amount          = $request->new_amount;
        $monthlySaving->old_amount      = $request->old_amount;
        $monthlySaving->new_amount      = $request->new_amount;
        $monthlySaving->is_indefinite   = $request->is_indefinite;
        $monthlySaving->revert_date     = $request->revert_date;
        $monthlySaving->done_by         = auth()->user()->ippis;
        $monthlySaving->save();

        $activityLog = new ActivityLog;
        $activityLog->logThis(null, $member->ippis, '(Changed monthly saving for ) '.$member->ippis.' '.$member->full_name, $request->new_obligation, 0, auth()->user()->ippis);

        Toastr::success('Monthly Contribution successfully changed', 'Success', ["positionClass" => "toast-bottom-right"]);
        return redirect()->route('members.savings', $ippis);
    }

}
