<?php
namespace App\Exports;

use App\LedgerSnapShot as LedgerSnapShotModel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class LedgerSnapShot implements FromCollection, WithHeadings
{
    public $id;

    function __construct(int $id) {
        $this->id = $id;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {

        $snapShot = LedgerSnapShotModel::find($this->id);
        // dd($snapShot);

        return collect($snapShot->exports);
    }

    /**
     * @return array
     */
    public function headings(): array {
        return [
            'IPPIS',
            'SURNAME',
            'OTHER NAMES',
            'SHARES',
            'MONTHLY SAVINGS CONTRIBUTION',
            'TOTAL SAVINGS',
            'CURRENT LONG TERM LOAN',
            'DURATION',
            'CURRENT LONG TERM BALANCE',
            'CURRENT SHORT TERM LOAN',
            'DURATION',
            'CURRENT SHORT TERM BALANCE',
            'CURRENT COMMODITY LOAN',
            'DURATION',
            'CURRENT COMMODITY BALANCE',
        ];
    }
}