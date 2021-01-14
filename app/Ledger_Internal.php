<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\LedgerInternalTransaction;
use Carbon\Carbon;
use App\Events\TransactionOccured;

class Ledger_Internal extends Model
{
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'use_centers_as_detail_accounts' => 'boolean',
    ];

    /**
     * Convert date_time to human readable format.
     *
     * @param  string  $value
     * @return string
     */
    public function getAccountNameAttribute($value)
    {

        return ucfirst(($value));
    }

    /**
     *  Get the parent of an account
     */
    function getParent() {
        return Ledger_Internal::where('id', $this->parent_id)->first();
    }

    /**
     *  Get the immediate children of an account
     */
    function getChildren() {
        return Ledger_Internal::where('parent_id', $this->id)->get();
    }

    /**
     *  Get the immediate children of an account
     */
    function getSiblings() {
        return Ledger_Internal::where('parent_id', $this->parent_id)->get();
    }

    /**
     *  Get ALL the descendants of an account
     */
    function getAllDescendants() {

        $strippedLedgerNumber = $this->extractSearchString();

        return Ledger_Internal::where('ledger_no', 'like', $strippedLedgerNumber.'%')->get();
    }

    /**
     *  Get the total balances of all the descendants of an account
     */
    function getBalanceOfAllDescendants(Carbon $dateFrom, Carbon $dateTo) {

        $strippedLedgerNumber = $this->extractSearchString();
        // dd($strippedLedgerNumber);

        $debits = LedgerInternalTransaction::where('ledger_no_dr', 'like', $strippedLedgerNumber.'%')->whereBetween('created_at', [$dateFrom, $dateTo])->sum('amount');

        $credits = LedgerInternalTransaction::where('ledger_no', 'like', $strippedLedgerNumber.'%')->whereBetween('created_at', [$dateFrom, $dateTo])->sum('amount');
        // dd($debits, $credits);

        // $dr = LedgerInternalTransaction::where('ledger_no_dr', 'like', str_replace('0', '', $this->ledger_no).'%')
        // ->get()
        // ->groupBy(function($date) {
        //     return Carbon::parse($date->created_at)->format('Y'); // grouping by years
        // })->map(function($traffic){
        //     return $traffic->groupBy(function($date){
        //         return Carbon::parse($date->created_at)->format('m'); // grouping by years
        //     });
        // });

        // $cr = LedgerInternalTransaction::where('ledger_no', 'like', str_replace('0', '', $this->ledger_no).'%')
        // ->get()
        // ->groupBy(function($date) {
        //     return Carbon::parse($date->created_at)->format('Y'); // grouping by years
        // })->map(function($traffic){
        //     return $traffic->groupBy(function($date){
        //         return Carbon::parse($date->created_at)->format('m'); // grouping by years
        //     });
        // });

        // return $debits - $credits;

        if($this->account_type == 'asset') {
            return $debits - $credits;
        } elseif ($this->account_type == 'expense') {
            return $debits - $credits;
        } elseif ($this->account_type == 'equity') {
            return $credits - $debits;
        } elseif ($this->account_type == 'income') {
            return $credits - $debits;
        } elseif ($this->account_type == 'liability') {
            return $credits - $debits;
        }  


        $allDescendants = $this->getAllDescendants();

        $balance = 0;
        foreach ($allDescendants as $descendant) {
            $balance +=  $descendant->getBalanceOnPositiveAccount();
        }

        return $balance;
    }

    /**
     * Return the begning part of a header account by removing the trailing zeros
     */
    function extractSearchString() {
        $splitted = str_split($this->ledger_no);
        $splittedCopy  = $splitted ;

        $i = count($splitted) - 1;
        while($splitted[$i] == 0) {
            unset($splittedCopy[$i]);
            $i--;
        }

        return implode('', $splittedCopy);
    }

    /**
     * Get total debits on an account
     */
    public function getTotalDebit() {
        return LedgerInternalTransaction::where('ledger_no_dr', $this->ledger_no)->sum('amount');
    }

    /**
     * Get total credits on an account
     */
    public function getTotalCredit() {
        return LedgerInternalTransaction::where('ledger_no', $this->ledger_no)->sum('amount');
    }

    /**
     * Get balance of an account based on account type
     */
    public function getLedgerAccountBalance() {
        $trxns = LedgerInternalTransaction::all();
        $debits = $trxns->where('ledger_no_dr', $this->ledger_no)->sum('amount');
        $credits = $trxns->where('ledger_no', $this->ledger_no)->sum('amount');

        if($this->account_type == 'asset') {
            return $debits - $credits;
        } elseif ($this->account_type == 'expense') {
            return $debits - $credits;
        } elseif ($this->account_type == 'equity') {
            return $credits - $debits;
        } elseif ($this->account_type == 'income') {
            return $credits - $debits;
        } elseif ($this->account_type == 'liability') {
            return $credits - $debits;
        }
    }




    // ===========================MONTHLY SAVINGS RECORDINGS===================================

    /**
     * Function to post a savings (dp_S) trxn to DB
     */
    public function recordDepositToSavings(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'dp_S')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }

        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName);
    
        $parent = $trxnType->associated_trxns['dr'];
        // $ledger_no_dr = $member->member_pay_point ? $member->member_pay_point->transacting_bank_ledger_no : $parent;

        $members_savings = $ledger_no;
        // $bank = $ledger_no_dr;
    
        event(new TransactionOccured($members_savings, $bank, microtime(), 'cr', 'dp_S', $member->ippis, 'saving', $amount, $description));

        return 1;
    }

    /**
     * Function to record withdrawal from savings (wd_S) trxn to DB
     */
    public function recordWithdrawalFromSavings(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'wd_S')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }

        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName) ;

        $parent = $trxnType->associated_trxns['cr'];
        // $ledger_no_dr = $member->member_pay_point ? $member->member_pay_point->transacting_bank_ledger_no : $parent;

        // $bank = $ledger_no_dr;
        $members_savings = $ledger_no;
    
        event(new TransactionOccured($bank, $members_savings, microtime(), 'cr', 'wd_S', $member->ippis, 'saving', $amount, $description));

        return 1;
    }

    /**
     * Function to record Interest on withdrawal from savings trxn to DB
     */
    public function recordSavingsWithdrawalInterest(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'wd_interest')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }
        
        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no_cr = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName);
        $ledger_no_dr = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName);

        $savings = $ledger_no_dr;
        $interest_account = $ledger_no_cr;

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured($interest_account, $savings, microtime(), 'cr', 'wd_interest', $member->ippis, 'wd_interest', $amount, $description));

        return 1;
    }

    /**
     * Function to record Processing Fee on Loan trxn to DB
     */
    public function recordSavingsWithdrawalProcessingFee(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'wd_processing_fee')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }
        
        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no_cr = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName);
        $ledger_no_dr = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName);

        $savings = $ledger_no_dr; // Savings account corresponding to member center
        $processing_fee_account = $ledger_no_cr;

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured($processing_fee_account, $savings, microtime(), 'cr', 'wd_processing_fee', $member->ippis, 'wd_processing_fee', $amount, $description));

        return 1;
    }

    /**
     * Function to record Bank Transfer Charge trxn to DB
     */
    public function recordSavingsWithdrawalBankTransferCharge(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'savings_transfer_charges')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }
        
        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no_cr = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName);
        $ledger_no_dr = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName);

        $savings = $ledger_no_dr; // Savings account corresponding to member center
        $transfer_fee_account = $ledger_no_cr;

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured($transfer_fee_account, $savings, microtime(), 'cr', 'savings_transfer_charges', $member->ippis, 'savings_transfer_charges', $amount, $description));

        return 1;
    }



    // ==========================LONG TERM LOAN RECORDINGS=================================

    /**
     * Function to record Long Term Loan (ltl) trxn to DB
     */
    public function recordLTL(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'ltl')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }
        
        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName) ;
    
        $parent = $trxnType->associated_trxns['cr'];
        // $ledger_no_dr = $member->member_pay_point ? $member->member_pay_point->transacting_bank_ledger_no : $parent;

        // $bank = $ledger_no_dr;
        $loan_balance = $ledger_no;

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured($bank, $loan_balance, microtime(), 'cr', 'ltl', $member->ippis, 'ltl', $amount, $description));

        return 1;
    }

    /**
     * Function to record Long Term Loan Repayment via Deposit (ltl_Rp_Deposit) trxn to DB
     */
    public function recordLTLRepaymentViaDeposit(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'ltl_Rp_Deposit')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }

        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName) ;
    
        $parent = $trxnType->associated_trxns['dr'];
        // $ledger_no_dr = $member->member_pay_point ? $member->member_pay_point->transacting_bank_ledger_no : $parent;

        // $bank = $ledger_no_dr;
        $loan_balance = $ledger_no;

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured($loan_balance, $bank, microtime(), 'cr', 'ltl_Rp_Deposit', $member->ippis, 'ltl_Rp_Deposit', $amount, $description));

        return 1;
    }

    /**
     * Function to record Long Term Loan Repayment via Savings (ltl_Rp_Savings) trxn to DB
     */
    public function recordLTLRepaymentViaSavings(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'ltl_Rp_Savings')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }
        
        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName) ;

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'ltl_Rp_Savings')->first();
        $ledger_no_dr = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName) ;

        $members_savings = $ledger_no_dr;
        $loan_balance = $ledger_no;
        // dd($loan_balance, $members_savings);

        event(new TransactionOccured($loan_balance, $members_savings, microtime(), 'cr', 'ltl_Rp_Savings', $member->ippis, 'ltl_Rp_Savings', $amount, $description));

        return 1;
    }

    /**
     * Function to record Interest on LTL trxn to DB
     */
    public function recordLTLInterest(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'ltl_interest')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }
        
        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no_cr = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName);
        $ledger_no_dr = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName);

        $ltl = $ledger_no_dr; // LTL account corresponding to member center
        $interest_account = $ledger_no_cr;

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured($interest_account, $ltl, microtime(), 'cr', 'ltl_interest', $member->ippis, 'ltl_interest', $amount, $description));

        return 1;
    }

    /**
     * Function to record Processing Fee on Loan trxn to DB
     */
    public function recordLTLProcessingFee(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'ltl_processing_fee')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }
        
        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no_cr = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName);
        $ledger_no_dr = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName);

        $ltl = $ledger_no_dr; // LTL account corresponding to member center
        $processing_fee_account = $ledger_no_cr;

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured($processing_fee_account, $ltl, microtime(), 'cr', 'ltl_processing_fee', $member->ippis, 'ltl_processing_fee', $amount, $description));

        return 1;
    }

    /**
     * Function to record Bank Transfer Charge trxn to DB
     */
    public function recordLTLBankTransferCharge(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'ltl_transfer_charges')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }
        
        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no_cr = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName);
        $ledger_no_dr = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName);

        $ltl = $ledger_no_dr; // LTL account corresponding to member center
        $transfer_fee_account = $ledger_no_cr;

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured($transfer_fee_account, $ltl, microtime(), 'cr', 'ltl_transfer_charges', $member->ippis, 'ltl_transfer_charges', $amount, $description));

        return 1;
    }

    /**
     * Function to record Long Term Loan Repayment via Savings (ltl_Rp_Savings) trxn to DB
     */
    public function recordLTLAdjustment(Member $member, $amount, String $description, $bank = NULL) : int {
        
        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'ltl_Adjustment')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }

        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no_cr = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName) ;
        $ledger_no_dr = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName) ;

        $ltl = $ledger_no_dr;
        $loan_balance = $ledger_no_cr;
        // dd($loan_balance, $bank, $amount);

        event(new TransactionOccured($loan_balance, $ltl, microtime(), 'cr', 'ltl_Adjustment', $member->ippis, 'ltl_Adjustment', $amount, $description));

        return 1;
    }




    // =========================SHORT TERM LOANS RECORDINGS =============================

    /**
     * Function to record Short Term Loan (stl) trxn to DB
     */
    public function recordSTL(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'stl')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }
        
        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName) ;
    
        $parent = $trxnType->associated_trxns['cr'];
        // $ledger_no_dr = $member->member_pay_point ? $member->member_pay_point->transacting_bank_ledger_no : $parent;

        // $bank = $ledger_no_dr;
        $loan_balance = $ledger_no;

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured($bank, $loan_balance, microtime(), 'cr', 'stl', $member->ippis, 'stl', $amount, $description));

        return 1;
    }

    /**
     * Function to record Long Term Loan Repayment via Deposit (ltl_Rp_Deposit) trxn to DB
     */
    public function recordSTLRepaymentViaDeposit(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'stl_Rp_Deposit')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }
        
        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName) ;
    
        $parent = $trxnType->associated_trxns['dr'];
        // $ledger_no_dr = $member->member_pay_point ? $member->member_pay_point->transacting_bank_ledger_no : $parent;

        // $bank = $ledger_no_dr;
        $loan_balance = $ledger_no;

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured($loan_balance, $bank, microtime(), 'cr', 'stl_Rp_Deposit', $member->ippis, 'stl_Rp_Deposit', $amount, $description));

        return 1;
    }

    /**
     * Function to record Long Term Loan Repayment via Savings (stl_Rp_Savings) trxn to DB
     */
    public function recordSTLRepaymentViaSavings(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'stl_Rp_Savings')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }
        
        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName) ;

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'stl_Rp_Savings')->first();
        $ledger_no_dr = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName) ;

        $members_savings = $ledger_no_dr;
        $loan_balance = $ledger_no;
        // dd($loan_balance, $members_savings);

        event(new TransactionOccured($loan_balance, $members_savings, microtime(), 'cr', 'stl_Rp_Savings', $member->ippis, 'stl_Rp_Savings', $amount, $description));

        return 1;
    }

    /**
     * Function to record Interest on STL trxn to DB
     */
    public function recordSTLInterest(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'stl_interest')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }
        
        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no_cr = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName);
        $ledger_no_dr = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName);

        $stl = $ledger_no_dr;
        $interest_account = $ledger_no_cr;

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured($interest_account, $stl, microtime(), 'cr', 'stl_interest', $member->ippis, 'stl_interest', $amount, $description));

        return 1;
    }

    /**
     * Function to record Processing Fee on Loan trxn to DB
     */
    public function recordSTLProcessingFee(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'Stl_processing_fee')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }
        
        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no_cr = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName);
        $ledger_no_dr = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName);

        $Stl = $ledger_no_dr; // STL account corresponding to member center
        $processing_fee_account = $ledger_no_cr;

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured($processing_fee_account, $Stl, microtime(), 'cr', 'Stl_processing_fee', $member->ippis, 'Stl_processing_fee', $amount, $description));

        return 1;
    }

    /**
     * Function to record Bank Transfer Charge trxn to DB
     */
    public function recordSTLBankTransferCharge(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'stl_transfer_charges')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }
        
        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no_cr = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName);
        $ledger_no_dr = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName);

        $stl = $ledger_no_dr; // STL account corresponding to member center
        $transfer_fee_account = $ledger_no_cr;

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured($transfer_fee_account, $stl, microtime(), 'cr', 'stl_transfer_charges', $member->ippis, 'stl_transfer_charges', $amount, $description));

        return 1;
    }

    /**
     * Function to record Long Term Loan Repayment via Savings (stl_Rp_Savings) trxn to DB
     */
    public function recordSTLAdjustment(Member $member, $amount, String $description, $bank = NULL) : int {
        
        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'stl_Adjustment')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }

        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no_cr = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName) ;
        $ledger_no_dr = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName) ;

        $stl = $ledger_no_dr; // STL account corresponding to member center
        $loan_balance = $ledger_no_cr;
        // dd($loan_balance, $bank, $amount);

        event(new TransactionOccured($loan_balance, $stl, microtime(), 'cr', 'stl_Adjustment', $member->ippis, 'stl_Adjustment', $amount, $description));

        return 1;
    }





    // ===============================COMMODITY LOANS RECORDINGS==============================

    /**
     * Function to record Commodity Loan (coml) trxn to DB
     */
    public function recordCOML(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'coml')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }

        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no_dr = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName) ;

        $stock_account = $trxnType->associated_trxns['cr'];

        $loan_balance = $ledger_no_dr;
        $sales = $stock_account;

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured($sales, $loan_balance, microtime(), 'cr', 'coml', $member->ippis, 'coml', $amount, $description));

        return 1;
    }

    /**
     * Function to record Commodity Loan Repayment via Deposit (coml_Rp_Deposit) trxn to DB
     */
    public function recordCOMLRepaymentViaDeposit(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'coml_Rp_Deposit')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }
        
        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName) ;
    
        $parent = $trxnType->associated_trxns['dr'];
        // $ledger_no_dr = $member->member_pay_point ? $member->member_pay_point->transacting_bank_ledger_no : $parent;

        // $bank = $ledger_no_dr;
        $loan_balance = $ledger_no;

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured($loan_balance, $bank, microtime(), 'cr', 'coml_Rp_Deposit', $member->ippis, 'coml', $amount, $description));

        return 1;
    }

    /**
     * Function to record Commodity Loan Repayment via Savings (coml_Rp_Savings) trxn to DB
     */
    public function recordCOMLRepaymentViaSavings(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'coml_Rp_Savings')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }
        
        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName) ;
    
        $ledger_no_dr = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName) ;

        $members_savings = $ledger_no_dr;
        $loan_balance = $ledger_no;

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured($loan_balance, $members_savings, microtime(), 'cr', 'coml_Rp_Savings', $member->ippis, 'coml', $amount, $description));

        return 1;
    }

    /**
     * Function to record Interest on COML trxn to DB
     */
    public function recordCOMLInterest(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'coml_interest')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }

        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName);

        $parent = $trxnType->associated_trxns['dr'];
        $ledger_no_dr = $member->member_pay_point ? $member->member_pay_point->transacting_bank_ledger_no : $parent;

        $coml = $ledger_no_dr;
        $interest_account = $ledger_no;

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured($interest_account, $coml, microtime(), 'cr', 'coml_interest', $member->ippis, 'coml_interest', $amount, $description));

        return 1;
    }

    /**
     * Function to post a shares (buy_shares) trxn to DB
     */
    public function recordSharesBought(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'buy_shares')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }

        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName);
    
        $parent = $trxnType->associated_trxns['dr'];
        // $ledger_no_dr = $member->member_pay_point ? $member->member_pay_point->transacting_bank_ledger_no : $parent;

        $shares_account = $ledger_no;
        // $bank = $ledger_no_dr;
    
        event(new TransactionOccured($shares_account, $bank, microtime(), 'cr', 'buy_shares', $member->ippis, 'buy_shares', $amount, $description));

        return 1;
    }

    /**
     * Function to post a shares (buy_shares) trxn to DB
     */
    public function recordSharesLiquidation(Member $member, $amount, String $description, $bank = NULL) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'buy_shares')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }

        $centerName = $member->member_pay_point ? $member->member_pay_point->name : '';
        $ledger_no = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName);
    
        $parent = $trxnType->associated_trxns['dr'];
        // $ledger_no_dr = $member->member_pay_point ? $member->member_pay_point->transacting_bank_ledger_no : $parent;

        $shares_account = $ledger_no;
        // $bank = $ledger_no_dr;
    
        event(new TransactionOccured($shares_account, $bank, microtime(), 'cr', 'buy_shares', $member->ippis, 'buy_shares', $amount, $description));

        return 1;
    }

    /**
     * Function to post NON remittance by IPPIS for savings
     */
    public function recordIPPISNonRemittanceSavings($amount, String $description, String $centerName) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'ippis_non_remittance_savings')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }

        $ledger_no_cr = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName);

        $ledger_no_dr = $trxnType->associated_trxns['dr'];

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured($ledger_no_cr, null, microtime(), 'cr', 'ippis_non_remittance_savings', null, 'ippis_non_remittance_savings', $amount, $description));

        return 1;
    }

    /**
     * Function to post NON remittance by IPPIS for savings
     */
    public function recordIPPISNonRemittanceLTL($amount, String $description, String $centerName) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'ippis_non_remittance_ltl')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }

        $ledger_no_dr = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName);

        $ledger_no_cr = $trxnType->associated_trxns['cr'];

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured(null, $ledger_no_dr, microtime(), 'cr', 'ippis_non_remittance_ltl', null, 'ippis_non_remittance_ltl', $amount, $description));

        return 1;
    }

    /**
     * Function to post NON remittance by IPPIS for savings
     */
    public function recordIPPISNonRemittanceSTL($amount, String $description, String $centerName) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'ippis_non_remittance_stl')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }

        $ledger_no_dr = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName);

        $ledger_no_cr = $trxnType->associated_trxns['cr'];

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured(null, $ledger_no_dr, microtime(), 'cr', 'ippis_non_remittance_stl', null, 'ippis_non_remittance_stl', $amount, $description));

        return 1;
    }

    /**
     * Function to post NON remittance by IPPIS for savings
     */
    public function recordIPPISNonRemittanceCOML($amount, String $description, String $centerName) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'ippis_non_remittance_coml')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }

        $ledger_no_dr = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName);

        $ledger_no_cr = $trxnType->associated_trxns['cr'];

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured(null, $ledger_no_dr, microtime(), 'cr', 'ippis_non_remittance_coml', null, 'ippis_non_remittance_coml', $amount, $description));

        return 1;
    }

    /**
     * Function to post NON remittance by IPPIS for savings
     */
    public function recordIPPISNonRemittanceTotal($amount, String $description) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'ippis_non_remittance_total')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }

        $ledger_no_dr = $trxnType->associated_trxns['dr'];

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured(NULL, $ledger_no_dr, microtime(), 'cr', 'ippis_non_remittance_total', null, 'ippis_non_remittance_total', $amount, $description));

        return 1;
    }

    /**
     * Function to post NON remittance by IPPIS for savings
     */
    public function recordIPPISRemittance($amount, String $description, String $bank) : int {

        $trxnType = TransactionType_Ext::where('xact_type_code_ext', 'ippis_remittance')->first();

        if (count($trxnType->associated_trxns) == 0) {
            return 0;
        }

        $ledger_no_cr = $trxnType->associated_trxns['cr'];

        // fire event to save trxn in ledger transactions
        event(new TransactionOccured($ledger_no_cr, $bank, microtime(), 'cr', 'ippis_remittance', null, 'ippis_remittance', $amount, $description));

        return 1;
    }
}
