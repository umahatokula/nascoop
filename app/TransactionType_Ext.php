<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Member;
use App\Center;

class TransactionType_Ext extends Model
{
    protected $table = 'transaction_type__ext';

    protected $fillable = ['associated_trxns->dr', 'associated_trxns->cr'];
    
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'associated_trxns' => 'json',
    ];

    // public function getDetailAccountForThisTransactionType($trxn_type, Member $member) : int {

    //     $parent = Ledger_Internal::where('ledger_no', $this->associated_trxns[$trxn_type])->first();
    //     $ledgerAccount = Ledger_Internal::where('parent_id', $parent->id)->where('account_name', 'like', '%'.$member->member_pay_point->name.'%')->first();
        
    //     return $ledgerAccount ? $ledgerAccount->ledger_no : $parent->ledger_no;
    // }

    public function getDetailAccountForThisTransactionType($trxn_type, String $center_name) : int {

        $parent = Ledger_Internal::where('ledger_no', $this->associated_trxns[$trxn_type])->first();

        if (!$parent) {
            return 0;
        }

        $ledgerAccount = Ledger_Internal::where('parent_id', $parent->id)->where('account_name', 'like', '%'.$center_name.'%')->first();
        
        return $ledgerAccount ? $ledgerAccount->ledger_no : $parent->ledger_no;
    }
}
