<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\IppisDeductionsImport;
use App\Ledger;
use Carbon\Carbon;
use App\IppisReconciledData;

class ProcessIPPISImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $import;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(IppisDeductionsImport $import)
    {
        $this->import = $import;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $ref = $this->import->ref;
        $done_by = $this->import->done_by;
        $deduction_for = Carbon::parse($this->import->deduction_for);


        $deductions = [];

        foreach ($this->import->imports as $row) {

            $ippis = $row[1];
            $amountDeductedByIppis = $row[3];

            if ($ippis) {              
                $ledger = new Ledger;
                $monthlyDeductions = $ledger->getMemberTotalMonthlyDeduction($ippis, $deduction_for, $done_by);

                $result = $ledger->executeDeductions($ippis, $amountDeductedByIppis, $monthlyDeductions, $ref, $deduction_for, $done_by);


                if($result['error']) {
                    $reconciled                             = new IppisReconciledData;
                    $reconciled->ippis_deductions_import_id = $this->import->id;
                    $reconciled->month                      = $deduction_for->format('m');
                    $reconciled->year                       = $deduction_for->format('Y');
                    $reconciled->ippis                      = $result['ippis'];
                    $reconciled->message                    = $result['message'];
                    $reconciled->save();
                    
                    $deductions[] = $result;
                } else {
                    $reconciled                   = new IppisReconciledData;
                    $reconciled->ippis_deductions_import_id = $this->import->id;
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
                    $reconciled->save();

                    $deductions[] = $result;
                }
                
            }

        }

        // dd($deductions);

        $this->import->reconciled = $deductions;
        $this->import->done_by = $done_by;
        $this->import->is_done = 1;
        $this->import->save();
    }
}
