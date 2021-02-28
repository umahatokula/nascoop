<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ledger_Internal;
use App\LedgerInternalTransaction;
use App\AccountType;
use App\ChartOfAccount;
use App\AccountTransaction;
use App\TransactionType_Ext;
use App\Center;
use Carbon\Carbon;
use App\Events\TransactionOccured;
use DB;
use Illuminate\Validation\ValidationException;
use App\Helpers\CollectionHelper;


class LedgerInternalController extends Controller
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
     *  Show form to create new ledger account
     */
    public function index() {
        return view('accounting.index');
    }


    /**
     *  Show balancesheet
     */
    public function balancesheet() {

        $months = [
            ['label' => 'January', 'value' => '01'],
            ['label' => 'February', 'value' => '02'],
            ['label' => 'March', 'value' => '03'],
            ['label' => 'April', 'value' => '04'],
            ['label' => 'May', 'value' => '5'],
            ['label' => 'June', 'value' => '6'],
            ['label' => 'July', 'value' => '7'],
            ['label' => 'August', 'value' => '8'],
            ['label' => 'September', 'value' => '9'],
            ['label' => 'October', 'value' => '10'],
            ['label' => 'November', 'value' => '11'],
            ['label' => 'December', 'value' => '12'],
        ];

        $years = [
            ['label' => '2020', 'value' => '2020'],
        ];

        if(request()->ajax()) {
            return response()->json([
                'message' => 'success',
                'data' => [
                    'months' => $months,
                    'years' => $years,
                ],
            ]);
        }

        $dateFrom = Carbon::today()->startOfYear();
        $dateTo = Carbon::today()->endOfYear();
        
		if (request('dateFrom')) {
            $dateFrom = Carbon::parse(request('dateFrom'));
            $dateTo = Carbon::parse(request('dateTo'));
        }
        // dd($dateFrom, $dateTo);

        $data['assetAccountBalances']     = $this->accountTypeBalance('asset', $dateFrom, $dateTo);
        $data['liabilityAccountBalances'] = $this->accountTypeBalance('liability', $dateFrom, $dateTo);
        $data['equityAccountBalances']    = $this->accountTypeBalance('equity', $dateFrom, $dateTo);

        $data['dateFrom'] = $dateFrom;
        $data['dateTo'] = $dateTo;

        return view('accounting.balancesheet', $data);
    }

    /**
     * Display Chart of Accounts
     */
    public function coa() {
        $coas = Ledger_Internal::all();

        if (request()->ajax()) {
            return response()->json([
                'message' => 'success',
                'data' => [
                    'coas' => $coas,
                ],
            ]);
        }

        return view('accounting/coa');
    }

    /**
     * Display Profit and Loss
     */
    public function profitAndLoss() {
        

        if(request()->ajax()) {
            return response()->json([
                'message' => 'success',
                'data' => [
                    
                ],
            ]);
        }
        
        $dateFrom = Carbon::today()->startOfYear();
        $dateTo = Carbon::today()->endOfYear();
        
		if (request('dateFrom')) {
            $dateFrom = Carbon::parse(request('dateFrom'));
            $dateTo = Carbon::parse(request('dateTo'));
        }

        $data['incomeAccountBalances']     = $this->accountTypeBalance('income', $dateFrom, $dateTo);
        $data['expenseAccountBalances'] = $this->accountTypeBalance('expense', $dateFrom, $dateTo);

        $data['dateFrom'] = $dateFrom;
        $data['dateTo'] = $dateTo;

        return view('accounting.profitandloss', $data);
    }

    /**
     * Display Journal Entries
     */
    public function journal() {

        $dateFrom = Carbon::today()->startOfYear();
        $dateTo = Carbon::today()->endOfYear();

        if(request()->ajax()) {

            $entries = LedgerInternalTransaction::with('ledger_dr', 'ledger_cr')->orderBy('created_at')->whereBetween('created_at', [$dateFrom, $dateTo])->get();

            // $grouped = $entries->mapToGroups(function ($item, $key) {
            //     return [$item['ledger_no'] => $item];
            // });
            // dd($grouped);

            // $multiplied = $entries->map(function ($item, $key) {

            //     $parent = $item->ledger_dr->getParent();

            //     if ($parent->use_centers_as_detail_accounts) {
            //         return 
            //     }
            //     return $item * 2;
            // });

            return response()->json([
                'message' => 'success',
                'data' => [
                    'entries' => $entries,
                ],
            ]);
        }

        return view('accounting.journal');
    }

    /**
     * Link accounts to Trxn Types
     */
    public function linkAccounts() {

        $centers = Center::all();

        // $detailAccounts = Ledger_Internal::where(['usage' => 'header'])->get();
        $detailAccounts = Ledger_Internal::all();

        $result = [];
        foreach($detailAccounts as $detailAccount) {
            $result[] = [
                'account_name' => $detailAccount->account_name .' - '.$detailAccount->ledger_no,
                'ledger_no' => $detailAccount->ledger_no
            ];
        }

        $detailAccounts = $result;

        $trxnTypes = TransactionType_Ext::all();

        if(request()->ajax()) {
            return response()->json([
                'message' => 'success',
                'data' => [
                    'centers'         => $centers,
                    'detail_accounts' => $detailAccounts,
                    'trxn_types'      => $trxnTypes,
                ],
            ]);
        }

        return view('accounting.linkaccounts');
    }


    /**
     *  Show form to create new ledger account
     */
    public function newAccount() {
        $accountTypes = AccountType::all();
        $usages = [
            ['label' => 'Detail', 'value' => 'detail'],
            ['label' => 'Header', 'value' => 'header']
        ];

        if(request()->ajax()) {
            return response()->json([
                'message' => 'success',
                'data' => [
                    'account_types' => $accountTypes,
                    'usages' => $usages
                ],
            ]);
        }

        $data['account_types'] = $accountTypes;

        return view('ledgerInternal.newaccount', $data);
    }


    /**
     * Save data to create new leder account in DB
     * 
     * @return Object
     */
    public function postNewAccount(Request $request) {
        // dd($request->all());

        $rules = [
            'ledger_no'    => 'required|unique:ledger__internals',
            'account_type' => 'required',
            'account_name' => 'required',
            'usage' => 'required',
            'description' => 'required',
        ];

        $messages = [
            'ledger_no.required'    => 'Please enter the account code',
            'ledger_no.unique'    => 'This account code is already taken',
            'account_type.required' => 'Please select the account type',
            'account_name.required' => 'Please enter the account name',
            'usage.required'        => 'Please select the usage',
            'description.required'  => 'Please the description',
        ];

        $this->validate($request, $rules, $messages);

        // ensure header account numbers end with a zero
        if($request->usage == 'header' && substr($request->ledger_no, -1, 1) != 0) {
            throw ValidationException::withMessages(['ledger_no' => 'Header accounts must end with a zero']); 
        }

        $parent    = Ledger_Internal::find($request->parent_id);
        $level     = $parent ? ($parent->level + 1) : 1;
        $parent_id = $parent ? $parent->id : 0;
        // dd($parent, $level, $parent_id);

        $ledgerAccount                                 = new Ledger_Internal;
        $ledgerAccount->ledger_no                      = $request->ledger_no;
        $ledgerAccount->account_type                   = $request->account_type;
        $ledgerAccount->account_name                   = $request->account_name;
        $ledgerAccount->usage                          = $request->usage;
        $ledgerAccount->allow_manual_journal_entries   = $request->allow_manual_journal_entries;
        $ledgerAccount->ignore_trailing_zeros          = $request->ignore_trailing_zeros;
        $ledgerAccount->use_centers_as_detail_accounts = $request->use_centers_as_detail_accounts;
        $ledgerAccount->show_in_report_as_header       = $request->show_in_report_as_header;
        $ledgerAccount->show_total_amount_in_report    = $request->show_in_report_as_header;
        $ledgerAccount->description                    = $request->description;
        $ledgerAccount->level                          = $level;
        $ledgerAccount->parent_id                      = $parent_id;
        $ledgerAccount->status                         = 1;
        $ledgerAccount->save();

        if ($request->usage == 'header' && $request->use_centers_as_detail_accounts) {
            $centers = Center::all();

            $counter = 1;
            foreach($centers as $center) {
                $subLedgerAccount                                 = new Ledger_Internal;
                $subLedgerAccount->ledger_no                      = $ledgerAccount->ledger_no + $counter;
                $subLedgerAccount->account_type                   = $ledgerAccount->account_type;
                $subLedgerAccount->account_name                   = $center->name;
                $subLedgerAccount->usage                          = 'detail';
                $subLedgerAccount->allow_manual_journal_entries   = 1;
                $subLedgerAccount->ignore_trailing_zeros          = 0;
                $subLedgerAccount->use_centers_as_detail_accounts = 0;
                $subLedgerAccount->show_in_report_as_header       = 0;
                $subLedgerAccount->show_total_amount_in_report    = 1;
                $subLedgerAccount->description                    = '';
                $subLedgerAccount->level                          = $ledgerAccount->level + 1;
                $subLedgerAccount->parent_id                      = $ledgerAccount->id;
                $subLedgerAccount->status                         = 1;
                $subLedgerAccount->save();

                $counter++;
            }
        }

        return response()->json([
            'message' => 'success',
            'ledger_account' => $ledgerAccount,
        ]);
    }

    /**
     * Suggest a ledger number using its parent
     */
    public function suggestLedgerNumber($parent_id) {

        $parent = Ledger_Internal::find($parent_id);
        $lastChild = $parent->getChildren() ? $parent->getChildren()->last() : null;

        return response()->json([
            'message' => 'success',
            'data' => [
                'ledger_no' => $lastChild ? $lastChild->ledger_no + 1 : $parent->ledger_no + 1
            ]
        ]);
    }


    /**
     * Get all the children of an account type
     * 
     * @return Array
     */
    public function accountTypeChildren($accountType) {
        
        $children = Ledger_Internal::where(['account_type' => $accountType, 'usage' => 'header'])->get();

        if (request()->json()) {
            return response()->json([
                'message' => "success",
                'data' => [
                    'children' => $children,
                    ]
            ]);
        }

        return $children;
    }



    /**
     * Save data to transaction_types_ex table
     * 
     * @return Object
     */
    public function postLinkAccounts(Request $request) {
        // dd($request->all());

        $rules = [
            'xact_type_code_ext'    => 'required',
            'associated_trxns'    => 'required',
        ];

        $messages = [
            'ledger_no.required'    => 'Please enter the account code',
        ];

        // $this->validate($request, $rules, $messages);

        $types = $request->all();

        foreach ($types as $type) {
                        
            $trxnTypes = DB::table('transaction_type__ext')
              ->where('xact_type_code_ext', $type['xact_type_code_ext'])
              ->update([
                  'associated_trxns' => json_encode($type['associated_trxns'])
                  ]);
              
        }

        return response()->json([
            'message' => 'success',
            'trxn_types' => $trxnTypes,
        ]);
    }


    /**
     *  Get the balance of an account all its children
     * @param $account_type, $dateFrom, $dateTo
     */
    public function accountTypeBalance($account_type, Carbon $dateFrom = null, Carbon $dateTo = null) {

        $accountTypes = AccountType::all();
        $ledgerAccounts = Ledger_Internal::all();
        $year = date('Y');

        $accountTypeHeaders = Ledger_Internal::where('account_type', $account_type)->where('show_in_report_as_header', 1)->get();
        // dd($accountTypeHeaders);

        $result = [];
        $accountTypeTotal = 0;
        foreach ($accountTypeHeaders as $header) {
            if($header->show_total_amount_in_report) {
                $balance = $header->getBalanceOfAllDescendants($dateFrom, $dateTo);
                // dd($balance);

                $result[] = [$header, $balance];
                $accountTypeTotal += $balance;
            }

        }

        $accountTypeData = $result;
        
        if(request()->ajax()) {
            return response()->json([
                'message' => 'success',
                'data' => [
                    'message' => 'success',
                    'account_type_data' => $accountTypeData,
                    'account_type_total' => $accountTypeTotal
                ],
            ]);
        }

        return [
            'message' => 'success',
            'account_type_data' => $accountTypeData,
            'account_type_total' => $accountTypeTotal
        ];
    }

    /**
     * Get the balance of an account
     */
    public function quickBalance(Request $request) {

        if(request()->ajax()) {

            // dd($request->all());
    
            $rules = [
                'ledger_no'    => 'required',
            ];
    
            $messages = [
                'ledger_no.required'    => 'Please enter the account code',
            ];
    
            $this->validate($request, $rules, $messages);

            $result = [];
            $total = 0;

            $ledger_no = request('ledger_no');
            $account = Ledger_Internal::where('ledger_no', $ledger_no)->first();

            if($account) {
                if($account->usage == 'detail') {
                    $balance = $account->getLedgerAccountBalance();

                    $result[] = [$account, $balance];
                    $total += $balance;
                }

                if($account->usage == 'header') {
                        // dd(count($account->getAllDescendants()));
                    // dd($account->getAllDescendants());
                    foreach($account->getAllDescendants() as $child) {
                        $balance = $child->getLedgerAccountBalance();

                        $result[] = [$child, $balance];
                        $total += $balance;
                    }
                    // dd($total);
                }
            }            

            return response()->json([
                'message' => 'success',
                'data' => [
                    'result' => $result,
                    'total' => $total,
                ],
            ]);
        }

        return view('accounting.quickbalance');
    }

    /**
     * Display form to make manual ledger entry
     */
    public function makeLedgerEntry(Request $request) {

        if(request()->ajax()) {

            // $accounts = Ledger_Internal::all();


            $accounts = Ledger_Internal::select('*', DB::raw('CONCAT(ledger_no, " - ", account_name) AS name'))
                ->where('usage', 'detail')
                ->get();



            return response()->json([
                'message' => 'success',
                'data' => [
                    'accounts' => $accounts,
                ],
            ]);
        }

        return view('accounting.makeledgerentry');
    }

    /**
     * Post a amanual account entry
     */
    public function makeLedgerEntryPost(Request $request) {

        // dd($request->all());

        $rules = [
            'debit_account'    => 'required',
            'credit_account' => 'required',
            'amount' => 'required',
            'entry_date' => 'required',
            'description' => 'required',
        ];

        $messages = [
            'debit_account.required'    => 'Please enter the account to debit',
            'credit_account.required' => 'Please select the account to credit',
            'amount.required' => 'Please enter the amount',
            'entry_date.required'        => 'Please select the booking date',
            'description.required'  => 'Please the description',
        ];

        $this->validate($request, $rules, $messages);

        $debit_account = $request->debit_account;
        $credit_account = $request->credit_account;
        $amount = $request->amount;
        $entry_date = $request->entry_date;
        $description = $request->description;


        // fire event to save trxn in ledger transactions
        event(new TransactionOccured($credit_account, $debit_account, microtime(), 'cr', 'manual_entry', null, null, $amount, $description, $entry_date));

        return 1;

    }

    /**
     * Generate Trial Balance
     */
    public function trialBalance() {

        // $results = DB::select( DB::raw("SELECT (SELECT SUM(amount) FROM ledger_internal_transactions WHERE ledger_no_dr LIKE '121%') AS total_debit, (SELECT SUM(amount) FROM ledger_internal_transactions WHERE ledger_no LIKE '121%') AS total_credit FROM ledger_internal_transactions"));


        $dateFrom = Carbon::today()->startOfYear();
        $dateTo = Carbon::today()->endOfYear();
        
		if (request('dateFrom')) {
            $dateFrom = Carbon::parse(request('dateFrom'));
            $dateTo = Carbon::parse(request('dateTo'));
        }

        $results = [];
        $acounts = Ledger_Internal::all();

        foreach ($acounts as $account) {
            if ($account->usage == 'header') {
                $trimmed = rtrim($account->ledger_no, 0);

                $cr = LedgerInternalTransaction::where('ledger_no', 'LIKE', $trimmed.'%')->whereBetween('created_at', [$dateFrom, $dateTo])->sum('amount');
                $dr = LedgerInternalTransaction::where('ledger_no_dr', 'LIKE', $trimmed.'%')->whereBetween('created_at', [$dateFrom, $dateTo])->sum('amount');
            } else {
                $cr = LedgerInternalTransaction::where('ledger_no', $account->ledger_no)->whereBetween('created_at', [$dateFrom, $dateTo])->sum('amount');
                $dr = LedgerInternalTransaction::where('ledger_no_dr', $account->ledger_no)->whereBetween('created_at', [$dateFrom, $dateTo])->sum('amount');
            }
            

            $accountType = $account->account_type;
            $bal = 0;

            switch ($accountType) {
                case 'asset':
                    $bal = $dr - $cr;
                    break;
                case 'liability':
                    $bal = $cr - $dr;
                    break;
                case 'equity':
                    $bal = $cr - $dr;
                    break;
                case 'income':
                    $bal = $cr - $dr;
                    break;
                case 'expense':
                    $bal = $dr - $cr;
                    break;
            }

            $results[] = [$account, $bal, $dr, $cr];
        }
        
        $trialBalances = $results;
    
        if(request()->ajax()) {
            return response()->json([
                'message' => 'success',
                'data' => [
                    'trial_balances' => $trialBalances,
                ],
            ]);
        }

        $data['trialBalances'] = $results;
        $data['dateFrom'] = $dateFrom;
        $data['dateTo'] = $dateTo;

        return view('accounting.trialBalance', $data);
    }

    /**
     * Generate Legder for an account ie all trxns that occured on an account
     * @param $account_code
     */
    public function accountLedger($account_code) {
        // dd(request('dateFrom'));

        $dateFrom = Carbon::today()->startOfYear();
        $dateTo = Carbon::today()->endOfYear();

        $results = [];
        $account = Ledger_Internal::where('ledger_no', $account_code)->first();

		if (request('dateFrom')) {
            $dateFrom = Carbon::parse(request('dateFrom'));
            $dateTo = Carbon::parse(request('dateTo'));
        }

        if ($account->usage == 'header') {
            $trimmed = rtrim($account->ledger_no, 0);

            $trxns = LedgerInternalTransaction::where('ledger_no', 'LIKE', $trimmed.'%')->orWhere('ledger_no_dr', 'LIKE', $trimmed.'%')->orderBy('created_at')->get();
            // $cr = LedgerInternalTransaction::where('ledger_no', 'LIKE', $trimmed.'%')->orderBy('created_at')->get();
            // $dr = LedgerInternalTransaction::where('ledger_no_dr', 'LIKE', $trimmed.'%')->orderBy('created_at')->get();
        } else {
            $trxns = LedgerInternalTransaction::where('ledger_no', $account->ledger_no)->orWhere('ledger_no_dr', $account->ledger_no)->orderBy('created_at')->get();
            // $cr = LedgerInternalTransaction::where('ledger_no', $account->ledger_no)->orderBy('created_at')->get();
            // $dr = LedgerInternalTransaction::where('ledger_no_dr', $account->ledger_no)->orderBy('created_at')->get();
        }

        $trxns = $trxns->whereBetween('created_at', [$dateFrom, $dateTo]);
        $data['total_dr'] = $trxns->where('ledger_no_dr', $account->ledger_no)->sum('amount');
        $data['total_cr'] = $trxns->where('ledger_no', $account->ledger_no)->sum('amount');
        // dd($trxns->where('ledger_no_dr', $account->ledger_no)->sum('amount'));

        // $concatenated = $cr->concat($dr);
        // $data['trxns'] = CollectionHelper::paginate($concatenated);
        $data['trxns'] = CollectionHelper::paginate($trxns);

        $data['account'] = $account;
        $data['dateFrom'] = $dateFrom;
        $data['dateTo'] = $dateTo;

        return view('accounting.accountLedger', $data);
    }

    /**
     * Generate the PDF for an account ledger
     * @param $account_code, $dateFrom, $dataTo
     */
    public function accountLedgerPdf($account_code, $dateFrom, $dateTo) {
        // dd(request('dateFrom'));

        $results = [];
        $account = Ledger_Internal::where('ledger_no', $account_code)->first();

        if ($account->usage == 'header') {
            $trimmed = rtrim($account->ledger_no, 0);

            $trxns = LedgerInternalTransaction::where('ledger_no', 'LIKE', $trimmed.'%')->orWhere('ledger_no_dr', 'LIKE', $trimmed.'%')->orderBy('created_at')->get();
        } else {
            $trxns = LedgerInternalTransaction::where('ledger_no', $account->ledger_no)->orWhere('ledger_no_dr', $account->ledger_no)->orderBy('created_at')->get();
        }

        $trxns = $trxns->whereBetween('created_at', [$dateFrom, $dateTo]);

        // $concatenated = $cr->concat($dr);
        // $data['trxns'] = CollectionHelper::paginate($concatenated);
        $data['trxns'] = $trxns;

        $data['account'] = $account;
        $data['dateFrom'] = $dateFrom;
        $data['dateTo'] = $dateTo;
        
        $pdf = \PDF::loadView('pdf.accountLedger', $data)->setPaper('a4', 'landscape');
        return $pdf->download('GeneralLedger.pdf');
    }

    /**
     * Generate the PDF for Trial balance
     * @param $dateFrom, $dataTo
     */
    public function trialBalancePdf($dateFrom, $dateTo) {
        // dd(request('dateFrom'));
        
		if ($dateFrom) {
            $dateFrom = Carbon::parse(request('dateFrom'));
            $dateTo = Carbon::parse(request('dateTo'));
        }

        $results = [];
        $acounts = Ledger_Internal::all();

        foreach ($acounts as $account) {
            if ($account->usage == 'header') {
                $trimmed = rtrim($account->ledger_no, 0);

                $cr = LedgerInternalTransaction::where('ledger_no', 'LIKE', $trimmed.'%')->whereBetween('created_at', [$dateFrom, $dateTo])->sum('amount');
                $dr = LedgerInternalTransaction::where('ledger_no_dr', 'LIKE', $trimmed.'%')->whereBetween('created_at', [$dateFrom, $dateTo])->sum('amount');
            } else {
                $cr = LedgerInternalTransaction::where('ledger_no', $account->ledger_no)->whereBetween('created_at', [$dateFrom, $dateTo])->sum('amount');
                $dr = LedgerInternalTransaction::where('ledger_no_dr', $account->ledger_no)->whereBetween('created_at', [$dateFrom, $dateTo])->sum('amount');
            }
            

            $accountType = $account->account_type;
            $bal = 0;

            switch ($accountType) {
                case 'asset':
                    $bal = $dr - $cr;
                    break;
                case 'liability':
                    $bal = $dr - $cr;
                    break;
                case 'equity':
                    $bal = $dr - $cr;
                    break;
                case 'income':
                    $bal = $dr - $cr;
                    break;
                case 'expense':
                    $bal = $dr - $cr;
                    break;
            }

            $results[] = [$account, $bal];
        }
        
        $trialBalances = $results;

        $data['trialBalances'] = $results;
        $data['dateFrom'] = $dateFrom;
        $data['dateTo'] = $dateTo;
        
        $pdf = \PDF::loadView('pdf.trialBalance', $data)->setPaper('a4', 'landscape');
        return $pdf->download('TrialBalance.pdf');
    }

    public function singleLegEntry() {        
        $data['accounts'] = Ledger_Internal::select('*', DB::raw('CONCAT(ledger_no, " - ", account_name) AS name'))
            ->where('usage', 'detail')
            ->pluck('name', 'ledger_no');
        
            $data['types'] = [
                'debit' => 'Debit',
                'credit' => 'Credit',
            ];

        return view('accounting.singleLeg', $data);
    }

    public function singleLegEntryPost(Request $request) {
        // dd($request->all());

        $rules = [
            'type' => 'required',
            'account_no'  => 'required',
            'amount'         => 'required',
            'description'    => 'required',
        ];

        $messages = [
            'type.required'  => 'Please select the trxn type',
            'account_no.required' => 'Please select the account to credit',
            'amount.required'         => 'Please enter the amount',
            'description.required'    => 'Please the description',
        ];

        $this->validate($request, $rules, $messages);

        if ($request->type == 'debit') {
            LedgerInternalTransaction::insert([
                'date_time'          => microtime(),
                'ledger_no_dr'       => $request->account_no,
                'amount'             => $request->amount,
                'description'        => $request->description,
            ]);
        }

        if ($request->type == 'credit') {
            LedgerInternalTransaction::insert([
                'ledger_no'          => $request->account_no,
                'date_time'          => microtime(),
                'amount'             => $request->amount,
                'description'        => $request->description,
            ]);
        }

        flash('Entry successful')->success();
        return redirect()->route('singleLegEntry');
    }

}
