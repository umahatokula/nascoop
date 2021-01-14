<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Center;
use App\Serialisers\CustomSerialiser;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MonthlyDeductionsExport;
use Importer;
use Exporter;

use App\Member;
use App\Ledger;
use App\IppisDeductionsExport;
use App\IppisExportData;
use Carbon\Carbon;
use App\IppisDeductionsImport;
use App\TempActivityLog;
use Toastr;

class IppisDeductionsExportController extends Controller
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


    public function exportToIppis() {

        $data['centers'] = Center::pluck('name', 'id');
        // $center_id = request('center_id') ? : 9;

        $dateFrom = Carbon::now()->startOfYear();
        $dateTo = Carbon::now()->endOfYear();

        // SET FILTER DATE IF AVAILABLE
        if (request('dateFrom')) {
            $dateFrom = request('dateFrom');
            $dateTo = request('dateTo');
        }


        $query = IppisDeductionsExport::query();

        // FILTER BY CENTER IF AVAILABLE
        // if (request('center_id')) {
        //     $center_id = request('center_id');
        //     $query = $query->where('center_id', $center_id);
        // }

        $query = $query->whereBetween('deduction_for', [$dateFrom, $dateTo]);

        $data['deductions'] = $query->get();

        $data['dateFrom']  = $dateFrom;
        $data['dateTo']    = $dateTo;
        // $data['center_id'] = $center_id;

        return view('deductions.exportToIppis', $data);
    }

    /**
     * Save request to generate IPPIS deduction file
     */
    public function exportToIppisPost(Request $request) {
        // dd($request->all());

        $rules = [
            // 'pay_point' => 'required',
            'deduction_for' => 'required',

        ];

        $messages = [
            // 'pay_point.required' => 'Please select a pay point',
            'deduction_for.required' => 'Please select the date(month) you are generating for',
        ];

        $this->validate($request, $rules, $messages);

        // $center = Center::find($request->pay_point);

        $IppisDeductionsExport                = new IppisDeductionsExport;
        $IppisDeductionsExport->deduction_for = $request->deduction_for;
        $IppisDeductionsExport->center_id     = $request->center_id;
        $IppisDeductionsExport->is_done       = 0;
        $IppisDeductionsExport->done_by       = auth()->user()->ippis;
        $IppisDeductionsExport->save();

        Toastr::success('Export file is being processed', 'Success', ["positionClass" => "toast-bottom-right"]);
        return redirect()->back();        
    }

    /**
     * Download IPPIS deduction file
     */
    public function downloadIppisDeductionFile($id, $month, $year) {

        return Excel::download(new MonthlyDeductionsExport($id), 'IPPIS_DEDUCTION_FOR_'.$month.'_'.$year.'.xlsx');
        
    }

    /**
     * Save request to generate IPPIS deduction file
     */
    public function generateIPPIDDeductionFile(Request $request) {

        $pendingDeduction = IppisDeductionsExport::where('is_done', 0)->first();
        // $pendingDeduction = IppisDeductionsExport::find(1);
        // dd($pendingDeduction);
        
        if ($pendingDeduction) {

            $pendingDeduction->is_done = 2;
            $pendingDeduction->save();

            $done_by = $pendingDeduction->done_by;
            $deduction_for = $pendingDeduction->deduction_for;

            // get members to generate for    
            $members = Member::where('is_active', 1)->orderBy('pay_point')->get();

            // generate for each member that hasnt been generated for selected month, year and IPPIS
            $deductions = [];
            foreach($members as $member) {

                    $ledger = new Ledger;
                    $result = $ledger->getMemberTotalMonthlyDeduction($member->ippis, $deduction_for, $done_by);

                    $IppisExportData = IppisExportData::where('ippis', $member->ippis)
                    ->where('month', $deduction_for->format('m'))
                    ->where('year', $deduction_for->format('Y'))
                    ->first();

                    // create if not already existing
                    if (!$IppisExportData) {
                        $IppisExportData = new IppisExportData;
                    } else {                                                    
                        $IppisExportData->ippis                     = $result['ippis'];
                        $IppisExportData->month                     = $result['month'];
                        $IppisExportData->year                      = $result['year'];
                        $IppisExportData->deduction_for             = $result['deduction_for'];
                        $IppisExportData->full_name                 = $result['full_name'];
                        $IppisExportData->pay_point                 = $result['pay_point'];
                        $IppisExportData->monthly_savings_amount    = $result['monthly_savings_amount'];
                        $IppisExportData->long_term_monthly_amount  = $result['long_term_monthly_amount'];
                        $IppisExportData->short_term_monthly_amount = $result['short_term_monthly_amount'];
                        $IppisExportData->commodity_monthly_amount  = $result['commodity_monthly_amount'];
                        $IppisExportData->total                     = $result['total'];
                        $IppisExportData->done_by                   = $done_by;
                        $IppisExportData->save();  
                    }    

                    $deductions[] = [
                        $result['ippis'], 
                        $result['full_name'], 
                        $result['pay_point'], 
                        $result['monthly_savings_amount'], 
                        $result['long_term_monthly_amount'], 
                        $result['short_term_monthly_amount'], 
                        $result['commodity_monthly_amount'], 
                        $result['total']
                    ];           
            }

            $pendingDeduction = IppisDeductionsExport::find($pendingDeduction->id);
            $pendingDeduction->exports = $deductions;
            $pendingDeduction->is_done = 1;
            $pendingDeduction->save();
        }

        return redirect()->back();        
    }

}
