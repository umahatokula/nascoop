<?php

namespace App\Charts;

use ConsoleTVs\Charts\BaseChart;
use Illuminate\Http\Request;
use Chartisan\PHP\Chartisan;
use App\Commodity;
use App\Center;

class CommodityLoansByCenter extends BaseChart
{
    
    /**
     * Handles the HTTP request for the given chart.
     * It must always return an instance of Chartisan
     * and never a string or an array.
     */
    public function handler(Request $request): Chartisan
    {

        $centers = Center::pluck('name', 'id');
        $commodityData = Commodity::get();
        $commodityData = $commodityData->groupBy('pay_point')
        ->map(function ($items) {
            $sum = 0;
            foreach ($items as $item) {
                $sum += $item->totalBalance($item->pay_point);
            }
            return $sum/10;
        });

        $commodityData = $commodityData->keyBy(function ($value, $key) use($centers) {
            if (isset($centers[$key])) {
                return $centers[$key];
            } else {
                return 'OTHERS';
            }
        });
        
        return Chartisan::build()
            ->labels($commodityData->keys()->toArray())
            ->dataset('Commodities Loans', $commodityData->values()->toArray());
    }
}
