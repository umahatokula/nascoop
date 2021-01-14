<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\PVNumber;

class ShortTerm extends Model
{
    protected $dates = ['loan_date', 'deposit_date', 'loan_end_date'];

    public function member()
    {
        return $this->belongsTo(Member::class, 'ippis', 'ippis');
    }

    public function doneBy()
    {
        return $this->belongsTo(Member::class, 'done_by', 'ippis');
    }
    
    public function payments()
    {
        return $this->hasMany(ShortTermPayment::class);
    }


    public function defaults()
    {
        return $this->hasMany(ShortTermLoanDefault::class);
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
     * Get total owed on long term loan
     */
    public function totalBalance($payPoint = null) {
        if ($payPoint) {
            $members = Member::where('pay_point', $payPoint)->with('short_term_payments')->get();
        } else {
            $members = Member::with('short_term_payments')->get();
        }
        
        // $members = Member::with('short_term_payments')->get();
        
        $total = $members->reduce(function ($carry, $item) {
            $bal = $item->short_term_payments->where('is_authorized', 1)->last() ? $item->short_term_payments->where('is_authorized', 1)->last()->bal : 0;
            return $carry + $bal;
        });

        return $total;
    }
    

    /**
     * Generate PV number for STL
     */
    public function pvNumberGenerator() {
        $pvNumber = PVNumber::where('trxn_type', 'STL')->latest('id')->first();

        $pvN = $pvNumber ? $pvNumber->pv_number + 1 : 1;

        // make pv number entry
        $pv = new PVNumber;
        $pv->pv_number = $pvN;
        $pv->trxn_type = 'STL';
        $pv->generated_by = auth()->user()->ippis;
        $pv->save();

        return 'STL/'.date('Y').'/'.$pvN;
    }
}
