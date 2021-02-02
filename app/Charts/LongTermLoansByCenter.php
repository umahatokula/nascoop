<?php

namespace App\Charts;

use ConsoleTVs\Charts\BaseChart;
use Illuminate\Http\Request;
use Chartisan\PHP\Chartisan;
use App\LongTerm;
use App\Center;

class LongTermLoansByCenter extends BaseChart
{
    
    /**
     * Handles the HTTP request for the given chart.
     * It must always return an instance of Chartisan
     * and never a string or an array.
     */
    public function handler(Request $request): Chartisan
    {

        $centers = Center::pluck('name', 'id');

        $longTermData = LongTerm::get();
        $longTermData = $longTermData->groupBy('pay_point')
        ->map(function ($items) {
            $sum = 0;
            foreach ($items as $item) {
                $sum += $item->totalBalance($item->pay_point);
            }
            return $sum/10;
        });

        $longTermData = $longTermData->keyBy(function ($value, $key) use($centers) {
            if (isset($centers[$key])) {
                return $centers[$key];
            } else {
                return 'OTHERS';
            }
        });

        return Chartisan::build()
            ->labels($longTermData->keys()->toArray())
            ->dataset('Long Term Loans', $longTermData->values()->toArray());
    }
}
