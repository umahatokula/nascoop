<?php

namespace App\Http\Controllers;

use App\Serialisers\CustomSerialiser;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReconciledDeductionsExport;
use Importer;
use Exporter;


use Illuminate\Http\Request;

use App\Member;
use App\Ledger;
use Carbon\Carbon;
use App\IppisDeductionsImport;
use App\TempActivityLog;
use App\Center;
use App\IppisDeduction;

use App\Jobs\ProcessIPPISImport;
use App\IppisReconciledData;
use App\IppisTrxn;
use App\IppisTrxnPayment;
use App\Ledger_Internal;


class IppisDeductionsImportController extends Controller
{
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => [
            'reconcileIppisImport'
        ]]);
    }

    /**
     * Show page where IPPIS deduction file can be downloaded
     */
    function importFromIppis() {

        $data['centers'] = Center::pluck('name', 'id');
        // $center_id = request('center_id') ? : 9;

        $dateFrom = Carbon::now()->startOfYear();
        $dateTo = Carbon::now()->endOfYear();

        // SET FILTER DATE IF AVAILABLE
        if (request('dateFrom')) {
            $dateFrom = request('dateFrom');
            $dateTo = request('dateTo');
        }


        $query = IppisDeductionsImport::query();

        // FILTER BY CENTER IF AVAILABLE
        // if (request('center_id')) {
        //     $center_id = request('center_id');
        //     $query = $query->where('center_id', $center_id);
        // }

        $query = $query->whereBetween('deduction_for', [$dateFrom, $dateTo]);

        $data['deductions'] = $query->get();
        // dd($data['deductions']);

        $data['dateFrom'] = $dateFrom;
        $data['dateTo'] = $dateTo;
        // $data['center_id'] = $center_id;

        return view('deductions.importFromIppis', $data);

    }

    /**
     * Import and compare file from iPPIS. EXCEL FILE MUST BE IN THE FORMAT [S/N, IPPIS, FULL NAME, AMOUNT]
     */
    function importFromIppisPost(Request $request) {
        // dd($request->all());

        // Ensure there are no pending trxns        
        if(Ledger::areTherePendingTransaction()) {
            flash('Please process all pending transactions first')->error();
            return redirect()->route('pendingTransactions');
        }

        $rules = [
            'file' => 'required',
            'deduction_for' => 'required',
            // 'center_id' => 'required',
            'ref' => 'required',

        ];

        $messages = [
            'file.required' => 'Please select a file',
            'deduction_for.required' => 'Please select deduction date',
            'center_id.required' => 'Please select a paypoint',
            'ref.required' => 'Please enter a description',
        ];

        $this->validate($request, $rules, $messages);

        // Ensure there are no pending trxns        
        if(Ledger::areTherePendingTransaction()) {
            flash('Please process all pending transactions first')->error();
            return redirect()->back();
        }

        
        // generate trxn number
        $ledger = new Ledger;
        $trxnNumber = $ledger->generateTrxnNumber();

        $excel = Importer::make('Excel');
        $excel->load(request()->file('file'));
        $rows = $excel->getCollection();

        $data = [];
        $totalDeduction = 0;
        foreach($rows as $row) {
            $row = array_slice($row, 0, 4);

            if (count($row) == 4) { // ensure the data is in the format S/N, IPPIS, NAME, AMOUNT
                // ensure IPPIS and Amount fields are numeric. This is like a check for valid data
                $ippis  = intval($row[1]);
                $amount = doubleval($row[3]);
                if (is_numeric($ippis) && is_numeric($amount)) {
                    $data[] = [$row[0], $row[1], $row[2], $row[3]];
                    $totalDeduction += $amount;
                }

            }

        }

        // save data in db to be processed via cron job
        if (!empty($data)) {
            $import = new IppisDeductionsImport;
            $import->imports         = $data;
            $import->deduction_for   = $request->deduction_for;
            $import->center_id       = $request->center_id;
            $import->ref             = $request->ref;
            $import->total_deduction = $totalDeduction;
            $import->done_by         = auth()->user()->ippis;
            $import->save();

            // ProcessIPPISImport::dispatch($import);
        }

        $deduction_for = $request->deduction_for;
        $oBDeduction_for = Carbon::parse($deduction_for);

        foreach(Center::all() as $center) {
            $ippisTrxnObj = IppisTrxn::where('center_id', $center->id)
            ->where('month', $oBDeduction_for->format('m'))
            ->where('year', $oBDeduction_for->format('Y'))
            ->first();

            if (!$ippisTrxnObj) {
                $ippisTrxnObj                = new IppisTrxn;
                $ippisTrxnObj->trxn_number   = $trxnNumber;
                $ippisTrxnObj->center_id     = $center->id;
                $ippisTrxnObj->month         = $oBDeduction_for->format('m');
                $ippisTrxnObj->year          = $oBDeduction_for->format('Y');
                $ippisTrxnObj->deduction_for = $deduction_for;
                $ippisTrxnObj->ms_dr         = 0;
                $ippisTrxnObj->ms_cr         = 0;
                $ippisTrxnObj->ms_bal        = 0;
                $ippisTrxnObj->ltl_dr        = 0;
                $ippisTrxnObj->ltl_cr        = 0;
                $ippisTrxnObj->ltl_bal       = 0;
                $ippisTrxnObj->stl_dr        = 0;
                $ippisTrxnObj->stl_cr        = 0;
                $ippisTrxnObj->stl_bal       = 0;
                $ippisTrxnObj->coml_dr       = 0;
                $ippisTrxnObj->coml_cr       = 0;
                $ippisTrxnObj->coml_bal      = 0;
                $ippisTrxnObj->done_by       = auth()->user()->ippis;
                $ippisTrxnObj->save();
            } 
        } 

        flash('Import successful')->success();
        return redirect()->back();

    }

    /**
     * Download IPPIS deduction as excel file
     */
    function downloadDeductionsFile($id, $month, $year) {

        return Excel::download(new ReconciledDeductionsExport($id), 'Reconciled_'.$month.'_'.$year.'.xlsx');

    }

    /*
     * Upload and reconcile deduction file returned by IPPIS
     */
    function reconcileIppisImport(Request $request, Ledger $ledger) {

        $tempLog = IppisDeductionsImport::where('is_done', 0)->first();
        // $tempLog = IppisDeductionsImport::latest()->first();
        // dd(is_null($tempLog));

        if(is_null($tempLog)) {
            return;
        }

        if ($tempLog) {

            // set is_done to a number other than 0 so that cron job does not pick it up as undone and start processing all over
            $tempLog->is_done = 2;
            $tempLog->save();

            $ref = $tempLog->ref;
            $done_by = $tempLog->done_by;
            $deduction_for = Carbon::parse($tempLog->deduction_for);

            // get all ippis in db that also came in returned IPPIS file, those in DB but not in IPPIS file and those in IPPIS file but not in db
            // $membersIPPIS = Member::where('pay_point', $tempLog->center_id)->pluck('ippis')->toArray();
            $members = Member::all();
            $membersIPPIS = $members->pluck('ippis')->toArray();

            $deductions = [];
            $totalAmountDeductedByIppis = 0;


            $importedIPPIS = [];
            foreach($tempLog->imports as $import) {
                $importedIPPIS['ippis'][] = $import[1];
                $importedIPPIS['amount'][] = $import[3];
            }

            // In returned IPPIS File but not in DB
            $inIPPISFile = array_diff($importedIPPIS['ippis'], $membersIPPIS);

            // In DB but not in returned IPPIS File
            $inDB = array_diff($membersIPPIS, $importedIPPIS['ippis']);

            foreach($members->groupBy('pay_point') as $centerId => $centerData) { // this is executed for every center that has a member
                $center = Center::find($centerId);

                // In both DB and returned IPPIS File
                $intersect = array_intersect($membersIPPIS, $importedIPPIS['ippis']);

                // get full array of imported IPPIS records that are in intersect
                $rows = [];
                foreach($tempLog->imports as $import) {
                    if(in_array($import[1], $intersect)) {
                        $rows[] = $import;
                    }
                }

                $remitted_savings           = 0;
                $remitted_ltl               = 0;
                $remitted_stl               = 0;
                $remitted_coml              = 0;

                foreach ($rows as $row) {

                    $ippis = $row[1];
                    $amountDeductedByIppis = $row[3];

                    if ($ippis) {              
                        // get details of expected amount for savings, ltl, stl, coml
                        $monthlyExpectedDeductions = Ledger::getMemberTotalMonthlyDeduction($ippis, $deduction_for, $done_by);

                        // perform deductions
                        $result = Ledger::executeDeductions($ippis, $amountDeductedByIppis, $monthlyExpectedDeductions, $ref, $tempLog->deduction_for, $done_by);

                        if(!$result['error']) {
                            // dd($result);
                            
                            $deductions[] = $result;
                            // dd($deductions);
                            
                            $reconciled                             = new IppisReconciledData;
                            $reconciled->ippis_deductions_import_id = $tempLog->id;
                            $reconciled->month                      = $deduction_for->format('m');
                            $reconciled->year                       = $deduction_for->format('Y');
                            $reconciled->ippis                      = $result['ippis'];
                            $reconciled->name                       = $result['name'];
                            $reconciled->expected_savings           = $result['expected_savings'];
                            $reconciled->remitted_savings           = $result['remitted_savings'];
                            $reconciled->expected_ltl               = $result['expected_ltl'];
                            $reconciled->remitted_ltl               = $result['remitted_ltl'];
                            $reconciled->expected_stl               = $result['expected_stl'];
                            $reconciled->remitted_stl               = $result['remitted_stl'];
                            $reconciled->expected_coml              = $result['expected_coml'];
                            $reconciled->remitted_coml              = $result['remitted_coml'];
                            $reconciled->message                    = $result['message'];
                            $reconciled->is_successful              = $result['is_successful'];
                            $reconciled->save();

                            // get total remitted savings, ltl. stl and coml for this upload. To be recorded in COA
                            $remitted_savings += floatval($result['remitted_savings']);
                            $remitted_ltl     += floatval($result['remitted_ltl']);
                            $remitted_stl     += floatval($result['remitted_stl']);
                            $remitted_coml    += floatval($result['remitted_coml']);
                        }                         
                    }
                }

                // Trigger event to save trxn in DB as deposit
                $totalAmountDeductedByIppis += ($remitted_savings + $remitted_ltl + $remitted_stl + $remitted_coml);
                if($remitted_savings > 0) {
                    $ledgerInternal = new Ledger_Internal;
                    $ledgerInternal->recordIPPISNonRemittanceSavings($remitted_savings, ' Savings (IPPIS Upload)', $center->name, $deduction_for);
                }
                if($remitted_ltl > 0) {
                    $ledgerInternal = new Ledger_Internal;
                    $ledgerInternal->recordIPPISNonRemittanceLTL($remitted_ltl, ' LTL (IPPIS Upload)', $center->name, $deduction_for);
                }
                if($remitted_stl > 0) {
                    $ledgerInternal = new Ledger_Internal;
                    $ledgerInternal->recordIPPISNonRemittanceSTL($remitted_stl, ' STL (IPPIS Upload)', $center->name, $deduction_for);
                }
                if($remitted_coml > 0) {
                    $ledgerInternal = new Ledger_Internal;
                    $ledgerInternal->recordIPPISNonRemittanceCOML($remitted_coml, ' COML (IPPIS Upload)', $center->name, $deduction_for);
                }
            }

            $inIPPISFileAmount = 0;
            foreach($inIPPISFile as $ippis) {

                $deductions[] = [
                        // 'ippis_deductions_import_id' => $tempLog->id,
                        // 'month'                      => $deduction_for->format('m'),
                        // 'year'                       => $deduction_for->format('Y'),
                        'ippis'                      => $ippis,
                        'name'                       => '',
                        'expected_savings'           => '',
                        'remitted_savings'           => '',
                        'expected_ltl'               => '',
                        'remitted_ltl'               => '',
                        'expected_stl'               => '',
                        'remitted_stl'               => '',
                        'expected_coml'              => '',
                        'remitted_coml'              => '',
                        'message'                    => 'Member not found in DB',
                        // 'is_successful'             => 0,
                ];
                $index = \array_search($ippis, $importedIPPIS['ippis']);
                $inIPPISFileAmount += doubleval($importedIPPIS['amount'][$index]);
            }

            foreach($inDB as $ippis) {

                $deductions[] = [
                        // 'ippis_deductions_import_id' => $tempLog->id,
                        // 'month'                      => $deduction_for->format('m'),
                        // 'year'                       => $deduction_for->format('Y'),
                        'ippis'                      => $ippis,
                        'name'                       => '',
                        'expected_savings'           => '',
                        'remitted_savings'           => '',
                        'expected_ltl'               => '',
                        'remitted_ltl'               => '',
                        'expected_stl'               => '',
                        'remitted_stl'               => '',
                        'expected_coml'              => '',
                        'remitted_coml'              => '',
                        'message'                    => 'Member not found in returned IPPIS File',
                        // 'is_successful'             => 0,
                ];
            }

            // Record overall amount in uploaded IPPIS file
            $totalDeductionExpectedFromIPPIS = $tempLog->total_deduction;
            if($totalDeductionExpectedFromIPPIS > 0) {
                $ledgerInternal = new Ledger_Internal;
                $ledgerInternal->recordIPPISNonRemittanceTotal($totalDeductionExpectedFromIPPIS, 'TOTAL EXPECTED FROM IPPPIS ('.$tempLog->ref.'-'.$deduction_for->format('m').'/'.$deduction_for->format('Y').')', $deduction_for);
            }

            $tempLog->reconciled = $deductions;
            $tempLog->done_by = $done_by;
            $tempLog->is_done = 1;
            $tempLog->save();
        }

    }

    /**
     * Show file where loan defaults can be treated. This feature will be on hold for now biko
     */
    function treatLoanDefaults() {
        return view('deductions.treatLoanDefaults');
    }
}
