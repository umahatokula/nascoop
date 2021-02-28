<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\PVNumber;
use DB;

class LongTerm extends Model
{
    protected $dates = ['loan_date', 'deposit_date', 'loan_end_date'];

    protected $casts = [
        'start_processing' => 'required',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'ippis', 'ippis');
    }

    public function doneBy()
    {
        return $this->belongsTo(Member::class, 'done_by', 'ippis');
    }

    public function loanGuarantor1()
    {
        return $this->belongsTo(Member::class, 'guarantor_1', 'ippis');
    }

    public function loanGuarantor2()
    {
        return $this->belongsTo(Member::class, 'guarantor_2', 'ippis');
    }

    public function payments()
    {
        return $this->hasMany(LongTermPayment::class);
    }


    public function defaults()
    {
        return $this->hasMany(LongTermLoanDefault::class);
    }

    /**
    * Check if a member has not finished paying loan after loan end date
    */
    public function checkLoanDefault()
    {
        $lastPayment = $this->payments->last();

        if (!is_null($this->loan_end_date)) {
            return ($this->loan_end_date->isPast() && $lastPayment->bal > 0) ? true : false;
        } else {
            return false;
        }
    }
    

    /**
     * Generate PV number for LTL
     */
    public function pvNumberGenerator() {
        $pvNumber = PVNumber::where('trxn_type', 'LTL')->latest('id')->first();

        $pvN = $pvNumber ? $pvNumber->pv_number + 1 : 1;

        // make pv number entry
        $pv = new PVNumber;
        $pv->pv_number = $pvN;
        $pv->trxn_type = 'LTL';
        $pv->generated_by = auth()->user()->ippis;
        $pv->save();

        return 'LTL/'.date('Y').'/'.$pvN;
    }
    
    /**
     * Get total owed on long term loan
     */
    public function totalBalance($payPoint = null) {
        if ($payPoint) {
            $members = Member::where('pay_point', $payPoint)->with('long_term_payments')->get();
        } else {
            $members = Member::with('long_term_payments')->get();
        }

        $total = $members->reduce(function ($carry, $member) {
            $bal = $member->long_term_payments->where('is_authorized', 1)->last() ? $member->long_term_payments->where('is_authorized', 1)->last()->bal : 0;
            return $carry + $bal;
        });

        return $total;
    }

    public function totalBalance1($payPoint = null) {
        $members = [];

        if ($payPoint) {
            $members = Member::where('pay_point', $payPoint)->with('ledgers')->select('ippis')->get()->toArray();

            return DB::table('long_term_payments')
                    ->join('members', 'members.ippis', '=', 'long_term_payments.ippis')
                    ->where('is_authorized', 1)
                    ->whereIn('long_term_payments.ippis', $members)
                    ->sum('bal');

        } else {

            return DB::table('long_term_payments')
                    ->join('members', 'members.ippis', '=', 'long_term_payments.ippis')
                    ->where('is_authorized', 1)
                    ->sum('bal');

        }
    }
}
