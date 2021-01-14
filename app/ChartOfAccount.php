<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Transaction;

class ChartOfAccount extends Model
{
    public function getAccountTotal($accountId) {

        return Transaction::where('chart_of_accounts_id', $accountId)->sum('amount');

    }
}
