<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Commodity extends Model
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
        return $this->hasMany(CommodityPayment::class);
    }


    public function defaults()
    {
        return $this->hasMany(CommodityLoanDefault::class);
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
    
    public function totalBalance($payPoint = null) {
        if ($payPoint) {
            $members = Member::where('pay_point', $payPoint)->with('commodities_loans_payments')->get();
        } else {
            $members = Member::with('commodities_loans_payments')->get();
        }
        
        $total = $members->reduce(function ($carry, $item) {
            // dd($carry, $item->commodities_loans_payments->last()->bal);
            $bal = $item->commodities_loans_payments->where('is_authorized', 1)->last() ? $item->commodities_loans_payments->where('is_authorized', 1)->last()->bal : 0;
            return $carry + $bal;
        });

        return $total;

        // $totalBalance = 0;
        // foreach($members as $member) {
        //     $totalBalance += $member->commodityLoanBalance();
        // }

        // return $totalBalance;
    }
}
