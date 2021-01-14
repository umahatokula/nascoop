<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ledger_Internal;
use App\AccountType;
use App\ChartOfAccount;
use Carbon\Carbon;

class Ledger_InternalController extends Controller
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
     *  List all account types and their children
     */
    public function index() {
        $data = [];
        $accountTypes = AccountType::all();
        $ledgerAccounts = Ledger_Internal::all();

        foreach ($accountTypes as $type) {
            $data[$type->account_type] = $this->accountTypeChildren($type->account_type);
        }

        // dd($data);
        return view('ledgerInternal.index', $data);
    }


    /**
     *  Show form to create new ledger account
     */
    public function newInternalLedgerAccount() {
        $accountTypes = AccountType::all();

        if(request()->ajax()) {
            return [
                'account_types' => $accountTypes,
            ];
        }

        $data['account_types'] = $accountTypes;

        return view('ledgerInternal.newaccount', $data);
    }


    /**
     * Save data to create new leder account in DB
     * 
     * @return Object
     */
    public function postNewInternalLedgerAccount(Request $request) {
        // dd($request->all());

        $parent    = Ledger_Internal::find($request->parent_id);
        $level     = $parent ? ($parent->level + 1) : 1;
        $parent_id = $parent ? $parent->id : 0;
        // dd($parent, $level, $parent_id);

        $ledgerAccount = new Ledger_Internal;
        $ledgerAccount->ledger_no    = $request->ledger_no;
        $ledgerAccount->account_type = $request->account_type;
        $ledgerAccount->account_name = $request->account_name;
        $ledgerAccount->level        = $level;
        $ledgerAccount->parent_id    = $parent_id;
        $ledgerAccount->status       = 1;
        $ledgerAccount->save();

        return response()->json([
            'ledger_account' => $ledgerAccount,
        ]);
    }

    /**
     * Get all the children of an account type
     * 
     * @return Array
     */
    public function accountTypeChildren($accountType) {
        
        $children = Ledger_Internal::where('account_type', $accountType)->get();

        if (request()->json()) {
            return ['children' => $children];
        }

        return $children;
    }

    /**
     *  show balance sheet
     */
    public function balanceSheet() {
        
		$dateFrom = null;
		$dateTo = Carbon::today();
        $accountTypes = AccountType::all();
        $ledgerAccounts = Ledger_Internal::all();

		// SET FILTER DATE IF AVAILABLE
		if (request('dateFrom') || request('dateTo')) {
			$dateFrom = Carbon::parse(request('dateFrom'));
            $dateTo = Carbon::parse(request('dateTo'));
            
            // Ensure future dates are not allowed
            if($dateFrom->isFuture() || $dateTo->isFuture()) {
                flash('Future dates are not allowed')->error();
                return redirect()->back();
            }
        }
        
        $data['currentAssets'] = $this->getSubAccountsByAccountNumber($ledgerAccounts, 15);
        $data['currentLiabilities'] = $this->getSubAccountsByAccountNumber($ledgerAccounts, 31);
        $data['dateFrom'] = $dateFrom;
        $data['dateTo'] = $dateTo;

        return view('ledgerInternal.balancesheet', $data);
    }


    /**
     * Get account's children
     */
    public function getSubAccountsByAccountNumber($ledgerAccounts, $ledger_no) {
        
        $result = [];
        $ledgerAccount = $ledgerAccounts->where('ledger_no', $ledger_no)->first();
        $result[] = $ledgerAccount;

        $accounts = $ledgerAccounts->where('parent_id', $ledgerAccount->id);

        foreach($accounts as $account) {
            $result[] = $account;
            $accounts = $ledgerAccounts->where('parent_id', $account->id);

            foreach ($accounts as $account) {
                $result[] = $account;
                $accounts = $ledgerAccounts->where('parent_id', $account->id);

                foreach ($accounts as $account) {
                    $result[] = $account;
                    $accounts = $ledgerAccounts->where('parent_id', $account->id);

                    foreach ($accounts as $account) {
                        $result[] = $account;
                        $accounts = $ledgerAccounts->where('parent_id', $account->id);

                        foreach ($accounts as $account) {
                            $result[] = $account;
                            $accounts = $ledgerAccounts->where('parent_id', $account->id);

                            foreach ($accounts as $account) {
                                $result[] = $account;
                                $accounts = $ledgerAccounts->where('parent_id', $account->id);
                            }
                        }
                    }
                }
            }

        }

        return $result;
    }

}
