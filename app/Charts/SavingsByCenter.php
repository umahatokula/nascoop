<?php

namespace App\Charts;

use ConsoleTVs\Charts\BaseChart;
use Illuminate\Http\Request;
use Chartisan\PHP\Chartisan;
use App\MonthlySavingsPayment;
use App\Center;

class SavingsByCenter extends BaseChart
{

    /**
     * Handles the HTTP request for the given chart.
     * It must always return an instance of Chartisan
     * and never a string or an array.
     */
    public function handler(Request $request): Chartisan
    {

        $centers = Center::pluck('name', 'id');
        
        // monthly savings chart
        $monthlySavingsData = MonthlySavingsPayment::get();
        $monthlySavingsData = $monthlySavingsData->groupBy('pay_point')
        ->map(function ($item) {
            return ($item->sum('cr'));
        });

        $monthlySavingsData = $monthlySavingsData->keyBy(function ($value, $key) use ($centers) {
            if (isset($centers[$key])) {
                return $centers[$key];
            } else {
                return 'OTHERS';
            }
        });

        return Chartisan::build()
            ->labels($monthlySavingsData->keys()->toArray())
            ->dataset('Monthly Savings', $monthlySavingsData->values()->toArray());
    }
}
