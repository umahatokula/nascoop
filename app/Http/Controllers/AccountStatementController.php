<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Account;
use App\AccountStatement;
use Carbon\Carbon;
use DB;

class AccountStatementController extends Controller
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


    function generateMonthlyStatement() {

			$accountTrxns = DB::table('account_transactions')
                   ->select('account_no', DB::raw('SUM(amount) as debits'))
                   ->groupBy('account_no');

			$accounts = DB::table('accounts')
							->joinSub($accountTrxns, 'accountTrxns', function ($join) {
									$join->on('accounts.account_no', '=', 'accountTrxns.account_no');
							})->get();

							dd($accounts);


    		
		// $dateFrom = Carbon::now()->startOfMonth();
		// $dateTo = Carbon::now()->endOfMonth();

		// $accounts = Account::all();

		// foreach ($accounts as $account) {

		// 	$totalDebits = $account->getTotalDebit($dateFrom, $dateTo);
		// 	$totalCredits = $account->getTotalCredit($dateFrom, $dateTo);
		// 	$closingBalance = $totalCredits - $totalDebits;

		// 	// dd($account->account_no, $totalDebits, $totalCredits);

		// 	AccountStatement::insert([
		// 		'account_no' => $account->account_no,
		// 		'date' => $dateTo,
		// 		'closing_balance' => $closingBalance,
		// 		'total_credit' => $totalCredits,
		// 		'total_debit' => $totalDebits,
		// 	]);

		// }
	}
    
}
