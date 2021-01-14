<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\IppisTrxn;
use App\IppisTrxnPayment;
use App\Ledger;
use Carbon\Carbon;
use App\Center;
use App\Ledger_Internal;
use App\TransactionType_Ext;
use App\Events\TransactionOccured;
use Toastr;

class IppisTrxnsController extends Controller
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

    public function index (Request $request) {
        $today = Carbon::today();

        $data['months'] = [
            '01'  => 'January',
            '02'  => 'February',
            '03'  => 'March',
            '04'  => 'April',
            '05'  => 'May',
            '06'  => 'June',
            '07'  => 'July',
            '08'  => 'August',
            '09'  => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        ];

        $years = [];
        for ($i=$today->format('Y') - 4; $i < $today->format('Y') + 1; $i++) { 
            $years[$i] = $i;
        }

        $data['years'] = $years;

        $month = $today->format('m');
        $year = $today->format('Y');
        
        $query = IppisTrxn::query();
        $query = $query->with('center');

        if ($request->month) {    
            $month = $request->month;
        }

        if ($request->year) {    
            $year = $request->year;
        }

        $query = $query->where('month', $month);
        $query = $query->where('year', $year);

        $data['month'] = $month;
        $data['year'] = $year;
        $data['trxns'] = $query->get();
        
        return view('ippistrxns.index', $data);
    }

    public function trxnDetails ($trxnID) {
        $data['trxn'] = IppisTrxn::find($trxnID);

        if (!$data['trxn']) {
            flash('Record not found')->error();
            return redirect()->route('ippis.trxns');
        }

        return view('ippistrxns.trxnDetails', $data);
    }

    public function debitBank() {
        return view('ippistrxns.debitBank');
    }


    /**
     * Function to return the data needed for TrxnDebit
     */
    public function debitBankData() {
        $centers = Center::all();
        $result = [];
        foreach ($centers as $c) {
            $result[] = [
                'id' => $c->id,
                'name' => $c->name.' ('. $c->code .')'
            ];
        }
        $centers = $result;


        $bankParents = Ledger_Internal::where('ledger_no', 121000)->first();
        $bankAccounts = $bankParents->getChildren();

        return response()->json([
            'centers' => json_encode($centers),
            'bank_accounts' => $bankAccounts,
        ]);
    }


    /**
     * Function to debit bank accounts and credit Saving, LTL, STL and COML respectively
     */
    public function debitBankPost (Request $request) {
        // dd($request->all());      

        $rules = [
            'ref'           => 'required',
            'center_id'     => 'required',
            'bank'          => 'required',
            'deduction_for' => 'required',
            'amount'        => 'numeric|required',

        ];

        $messages = [
            'ref.required'           => 'The description is required',
            'center_id.required'     => 'This field is required',
            'bank.required'          => 'This field is required',
            'deduction_for.required' => 'This field is required',
            'amount.required'        => 'Please enter the amount',
        ];

        $this->validate($request, $rules, $messages);

        $deduction_for = Carbon::parse($request->deduction_for);
        $centerID      = $request->center_id;
        $bank          = $request->bank;
        $amount        = $request->amount;
        $ref           = $request->ref;

        // generate trxn number
        $ledger = new Ledger;
        $trxnNumber = $ledger->generateTrxnNumber();

        // get center
        $center = Center::find($centerID);

        
        $ippisTrxnObj = IppisTrxn::where('center_id', $centerID)
        ->where('month', $deduction_for->format('m'))
        ->where('year', $deduction_for->format('Y'))
        ->first();

        if (!$ippisTrxnObj) {
            flash('No Entry found for selected month and year')->error();
            return;
        }
        
        // share the amount that came in order of priority = savings, ltl, stl and coml
        $deductions['ms_amount']   = $ippisTrxnObj->ms_bal;
        $deductions['ltl_amount']  = $ippisTrxnObj->ltl_bal;
        $deductions['stl_amount']  = $ippisTrxnObj->stl_bal;
        $deductions['coml_amount'] = $ippisTrxnObj->coml_bal;

        $shareMoney = $this->shareTheMoney($amount, $deductions);

        $ippisTrxnObj->trxn_number  = $trxnNumber;
        $ippisTrxnObj->ms_cr       += $shareMoney['ms_amount'];
        $ippisTrxnObj->ms_bal      -= $shareMoney['ms_amount'];
        $ippisTrxnObj->ltl_cr      += $shareMoney['ltl_amount'];
        $ippisTrxnObj->ltl_bal     -= $shareMoney['ltl_amount'];
        $ippisTrxnObj->stl_cr      += $shareMoney['stl_amount'];
        $ippisTrxnObj->stl_bal     -= $shareMoney['stl_amount'];
        $ippisTrxnObj->coml_cr     += $shareMoney['coml_amount'];
        $ippisTrxnObj->coml_bal    -= $shareMoney['coml_amount'];
        $ippisTrxnObj->save();


        // save as lone trxn entry...this is diff from IppisTrxn because it doesn't update the existing record. It makes a new entry
        $lastPayment = IppisTrxnPayment::where('center_id', $centerID)->latest('id')->first();

        $ippisTrxnPayment                = new IppisTrxnPayment;
        $ippisTrxnPayment->ippis_trxn_id = $ippisTrxnObj->id;
        $ippisTrxnPayment->trxn_number   = $trxnNumber;
        $ippisTrxnPayment->center_id     = $centerID;
        $ippisTrxnPayment->month         = $deduction_for->format('m');
        $ippisTrxnPayment->year          = $deduction_for->format('Y');
        $ippisTrxnPayment->deduction_for = $deduction_for;
        $ippisTrxnPayment->ms_cr         = $shareMoney['ms_amount'];
        $ippisTrxnPayment->ms_bal        = $lastPayment ? $lastPayment->ms_bal - $shareMoney['ms_amount'] : $shareMoney['ms_amount'];
        $ippisTrxnPayment->ltl_cr        = $shareMoney['ltl_amount'];
        $ippisTrxnPayment->ltl_bal       = $lastPayment ? $lastPayment->ltl_bal - $shareMoney['ltl_amount'] : $deductions['ltl_amount'] -$shareMoney['ltl_amount'];
        $ippisTrxnPayment->stl_cr        = $shareMoney['stl_amount'];
        $ippisTrxnPayment->stl_bal       = $lastPayment ? $lastPayment->stl_bal - $shareMoney['stl_amount'] : $deductions['stl_amount'] -$shareMoney['stl_amount'];
        $ippisTrxnPayment->coml_cr       = $shareMoney['coml_amount'];
        $ippisTrxnPayment->coml_bal      = $lastPayment ? $lastPayment->coml_bal - $shareMoney['coml_amount'] : $deductions['coml_amount'] -$shareMoney['coml_amount'];
        $ippisTrxnPayment->save();

        // Trigger event to save trxn in DB as deposit
        if($amount > 0) {
            $ledgerInternal = new Ledger_Internal;
            $ledgerInternal->recordIPPISRemittance($amount, $deduction_for->format('m').'/'.$deduction_for->format('Y').' '.$center->name.' IPPIS Remittance', $bank);
        }

        return $ippisTrxnObj;
    }


    /**
     * Function to share the money that was actually deposited into Savings, LTL, STL and COML respectively
     */
    public function shareTheMoney ($money, $deductions) : Array {

        $MSAmount   = $deductions['ms_amount'];
        $LTLAmount  = $deductions['ltl_amount'];
        $STLAmount  = $deductions['stl_amount'];
        $COMMAmount = $deductions['coml_amount'];

        $shareMoney['ms_amount']   = 0;
        $shareMoney['ltl_amount']  = 0;
        $shareMoney['stl_amount']  = 0;
        $shareMoney['coml_amount'] = 0;

        if($money > 0) {

            if ($money >= $MSAmount) {
                $shareMoney['ms_amount'] += $MSAmount;
                $money = $money - $MSAmount;
            } else {
                $shareMoney['ms_amount'] += $money;
                $money = $money - $money;
            }

            if($money >= $LTLAmount) {
                $shareMoney['ltl_amount'] += $LTLAmount;
                $money = $money - $LTLAmount;
            } else {

                $shareMoney['ltl_amount'] += $money;
                $money = $money - $money;
            }

            if($money >= $STLAmount) {
                $shareMoney['stl_amount'] += $STLAmount;
                $money = $money - $STLAmount;
            } else {

                $shareMoney['stl_amount'] += $money;
                $money = $money - $money;
            }

            if($money >= $COMMAmount) {
                $shareMoney['coml_amount'] += $COMMAmount;
                $money = $money - $COMMAmount;
            } else {
                
                $shareMoney['coml_amount'] += $money;
                $money = $money - $money;
            }


            // $shareMoney['ms_amount'] += $money;
        }

        return $shareMoney;
    }
}
