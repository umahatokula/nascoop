<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MonthlySaving extends Model
{
    public function payments()
    {
        return $this->hasMany(MonthlySavingsPayment::class);
    }

    public function doneBy()
    {
        return $this->belongsTo(Member::class, 'done_by', 'ippis');
    }
    
    /**
     * Get total monthly savings
     */
    public function totalBalance($payPoint = null) {
        if ($payPoint) {
            $members = Member::where('pay_point', $payPoint)->with('ledgers')->get();
        } else {
            $members = Member::with('ledgers')->get();
        }
        
        return $members->reduce(function ($carry, $member) {
            $bal = $member->monthly_savings_payments->where('is_authorized', 1)->last() ? $member->monthly_savings_payments->where('is_authorized', 1)->last()->bal : 0;
            return $carry + $bal;
        });
    }
}
