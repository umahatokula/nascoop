<?php

namespace App\Http\Controllers;

use App\MonthlySaving;
use App\MonthlySavingsPayment;
use App\Ledger;
use App\LongTerm;
use App\LongTermPayment;
use App\ShortTerm;
use App\ShortTermPayment;
use App\Commodity;
use App\CommodityPayment;
use App\Center;
use App\Member;
use App\Share;

use Illuminate\Http\Request;
use App\Charts\SavingsByCenter;
use App\Charts\LongTermLoansByCenter;
use App\Charts\ShortTermLoansByCenter;
use App\Charts\CommodityLoansByCenter;
use App\Charts\NumberOfMembersByCenter;

use App\Ledger_Internal;
use App\LedgerInternalTransaction;
use App\AccountTransaction;


class HomeController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index() {

        // redirect members to personal ledger
        $roles = auth()->user()->getRoleNames();

        if($roles->count() === 1 && $roles[0] == 'member') {
            return redirect()->route('members.ledger', auth()->user()->ippis);
        }
        
        // monthly savings
        $monthlySavings = new SavingsByCenter;
        $data['monthlySavings'] = $monthlySavings;        
        // $data['monthlySavings'] = [];
        
        // long term loans chart
        $longTermChart = new LongTermLoansByCenter;
        $data['longTermChart'] = $longTermChart;        
        // $data['longTermChart'] = [];
        
        // short term loans chart
        $shortTermChart = new ShortTermLoansByCenter;
        $data['shortTermChart'] = $shortTermChart;        
        // $data['shortTermChart'] = [];
        
        // commodity loans chart
        $commodityChart = new CommodityLoansByCenter;
        $data['commodityChart'] = $commodityChart;        
        // $data['commodityChart'] = [];
        
        $data['totalSavings']   = 0;
        $data['totalLTL']       = 0;
        $data['totalSTL']       = 0;
        $data['totalCommodity'] = 0;

        // number of members by center chart
        $membersByCenterData = Member::join('centers', 'centers.id', '=', 'members.pay_point')
                                ->orderBy('centers.name')
                                ->select('members.*') //see PS:
                                ->get();

        $membersByCenterData = $membersByCenterData->groupBy('pay_point');

        $centers = Center::all();

        foreach($membersByCenterData as $center_id => $item) {

            $center = $centers->first(function ($value, $key) use($center_id) {
                return $value->id == $center_id;
            });

            $savings = (new MonthlySaving)->totalBalance($center_id);
            $data['totalSavings'] += $savings;

            $totalLTL = (new LongTerm)->totalBalance($center_id);
            $data['totalLTL'] += $totalLTL;

            $totalSTL = (new ShortTerm)->totalBalance($center_id);
            $data['totalSTL'] += $totalSTL;
            
            $totalCOML = (new Commodity)->totalBalance($center_id);
            $data['totalCommodity'] += $totalCOML;

            $centerTotals[$center->name] = ['savingsTotalBalance' => $savings, 'ltlTotalBalance' => $totalLTL, 'stlTotalBalance' => $totalSTL, 'comlTotalBalance' => $totalCOML, 'numberInCenter' => count($item)];

        }

        $data['centerTotals'] = $centerTotals;
        
        // SHARE
        $data['shares'] = Share::sum('units');

        return view('home', $data);
    }
}
