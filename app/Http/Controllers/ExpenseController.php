<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\TransactionOccured;
use App\Expense;
use App\Ledger_Internal;
use App\Supplier;
use Carbon\Carbon;
use DB;

class ExpenseController extends Controller
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


    public function index() {

        $dateFrom = Carbon::today()->startOfYear();
        $dateTo = Carbon::today()->endOfYear();

        $expensesQuery = Expense::query();

        if (request('dateFrom')) {
            $dateFrom = request('dateFrom');
            $dateTo = request('dateTo');
        }

        if ($dateFrom == $dateTo) {
            $expensesQuery = $expensesQuery->where('date', $dateFrom);
        } else {
            $expensesQuery = $expensesQuery->whereBetween('date', [$dateFrom, $dateTo]);
        }

        $data['dateFrom'] = $dateFrom;
        $data['dateTo'] = $dateTo;
        // dd($dateFrom, $dateTo, $expensesQuery->paginate(20));

        $data['expenses'] = $expensesQuery->orderBy('created_at', 'desc')->paginate(20);

        return view('expenses.index', $data);
    }

    /**
     * Record new expense
     */
    public function newExpense() {

        // $accounts = Ledger_Internal::all();
        $data['suppliers'] = Supplier::select('*', DB::raw('CONCAT(fname, " ", lname) AS name'))->pluck('name', 'id');

        $expense_accounts = Ledger_Internal::select('*', DB::raw('CONCAT(ledger_no, " - ", account_name) AS name'))
            ->where('usage', 'detail')
            ->where('account_type', 'expense')
            ->pluck('name', 'ledger_no');

        $accounts = Ledger_Internal::select('*', DB::raw('CONCAT(ledger_no, " - ", account_name) AS name'))
            ->where('usage', 'detail')
            ->where('account_type', '<>', 'expense')
            ->pluck('name', 'ledger_no');

        if(request()->ajax()) {
            return response()->json([
                'message' => 'success',
                'data' => [
                    'expense_accounts' => $expense_accounts,
                    'accounts' => $accounts,
                ],
            ]);
        }

        $data['expense_accounts'] = $expense_accounts;
        $data['accounts'] = $accounts;
        
        return view('expenses.newExpense', $data);
    }


    /**
     * Post a expense account entry
     */
    public function newExpensePost(Request $request) {

        // dd($request->all());

        $rules = [
            'debit_account'  => 'required',
            'credit_account' => 'required',
            'amount'         => 'required',
            'date'           => 'required',
            'description'    => 'required',
        ];

        $messages = [
            'debit_account.required'  => 'Please enter the account to debit',
            'credit_account.required' => 'Please select the account to credit',
            'amount.required'         => 'Please enter the amount',
            'date.required'           => 'Please select the transaction date',
            'description.required'    => 'Please the description',
        ];

        $this->validate($request, $rules, $messages);

        $debit_account  = $request->debit_account;
        $credit_account = $request->credit_account;
        $amount         = $request->amount;
        $date           = $request->date;
        $description    = $request->description;
        $supplier_id    = $request->supplier_id;
        
        // storoe in DB
        $expense = new Expense;
        $expense->trxn_number    = $expense->generateTrxnNumber();
        $expense->debit_account  = $debit_account;
        $expense->credit_account = $credit_account;
        $expense->amount         = $amount;
        $expense->description    = $description;
        $expense->supplier_id    = $supplier_id;
        $expense->date           = $date;
        $expense->is_authorized  = 0;
        $expense->pv_number      = $expense->pvNumberGenerator();
        $expense->done_by        = auth()->user()->ippis;
        $expense->save();

        return redirect('accounting/expenses');

    }

    public function authorizeTransaction($trxn_number, $status) {

        $expense = Expense::where('trxn_number', $trxn_number)->first();

        if($status == 1) {
            $expense->is_authorized  = 1;

            // f
            event(new TransactionOccured($expense->credit_account, $expense->debit_account, microtime(), 'cr', 'manual_entry', null, null, $expense->amount, $expense->description, $expense->date));

        } else {
            $expense->is_authorized  = 2;
        }
        $expense->save();

        return redirect('accounting/expenses');
    }

    public function pv($trxn_number) {
        $data['expense'] = Expense::where('trxn_number', $trxn_number)->first();

        return view('expenses.pv', $data);
    }

    public function expensesPvPdf($trxn_number) {
        $expense = Expense::where('trxn_number', $trxn_number)->first();
        $data['expense'] = $expense;

        $pdf = \PDF::loadView('pdf.expense_pv', $data)->setPaper('a4', 'portrait');
        return $pdf->download('EXPENSE_'.$expense->pv_number.'.pdf');
    }
}
