<?php
namespace App\Exports;

use App\Center;
use App\Member;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MembersRegisterExport implements FromCollection, WithHeadings
{
    public $center_id;

    function __construct($center_id) {
        $this->center_id     = $center_id;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection() {

        if ($this->center_id) {
            return \DB::table('members')
                    ->join('centers', 'members.pay_point', '=', 'centers.id')
                    ->select('ippis', 'full_name', 'centers.name', 'email', 'phone', 'coop_no', 'nok_name', 'nok_phone', 'nok_rship', 'nok_address')
                    ->where('pay_point', $this->center_id)
                    ->get();
        }

        return \DB::table('members')
                ->join('centers', 'members.pay_point', '=', 'centers.id')
                ->select('ippis', 'full_name', 'centers.name', 'email', 'phone', 'coop_no', 'nok_name', 'nok_phone', 'nok_rship', 'nok_address')
                ->get();
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
            'EMAIL',
            'PHONE',
            'COOPERATIVE NUMBER',
            'NEXT-OF-KIN NAME',
            'NEXT-OF-KIN PHONE',
            'NEXT-OF-KIN RELATIONSHIP',
            'NEXT-OF-KIN ADDRESS',
        ];
    }

}