<?php
namespace App\Exports;

use App\Center;
use App\Member;
use App\Ledger;
use App\IppisDeductionsExport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Events\IPPISExportFileGenerated;
use App\IppisExportData;
use Carbon\Carbon;

use Illuminate\Support\Arr;

class MonthlyDeductionsExport implements FromCollection, WithHeadings
{
    public $id;
    public $deduction_for;

    function __construct($id) {
        $this->id     = $id;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection() {

        $deduction = IppisDeductionsExport::find($this->id);
        // dd($deduction);

        return collect($deduction->exports);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'IPPIS',
            'NAME',
            'PAY POINT',
            'SAVINGS',
            'LONG TERM LOAN',
            'SHORT TERM LOAN',
            'COMMODITY LOAN',
            'TOTAL',
        ];
    }

}