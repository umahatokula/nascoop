<?php
namespace App\Exports;

use App\Center;
use App\Member;
use App\Ledger;
use App\Jobs\ActivityJob;
use App\TempActivityLog;
use App\IppisDeductionsImport;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\IppisReconciledData;
use Carbon\Carbon;
use DB;

class ReconciledDeductionsExport implements FromView
{
    public $id;

    function __construct(int $id) {
        $this->id = $id;
    }
    
    public function view(): View
    {
        return view('exports.deductions', [
            'deductions' => IppisDeductionsImport::find($this->id)
        ]);
    }
}