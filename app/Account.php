<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\AccountTransaction;
use Carbon\Carbon;

class Account extends Model
{
	
    /**
     * Get total debits on an account
     */
    public function getTotalDebit(Carbon $dateFrom, Carbon $dateTo) {
        return AccountTransaction::where('account_no', $this->account_no)
        ->where('xact_type_code', 'cr')
        ->sum('amount');
    }

    /**
     * Get total credits on an account
     */
    public function getTotalCredit(Carbon $dateFrom, Carbon $dateTo) {
        return AccountTransaction::where('account_no', $this->account_no)
        ->where('xact_type_code', 'dr')
        ->sum('amount');
    }
}
