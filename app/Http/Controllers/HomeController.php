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
        
        $monthlySaving = new MonthlySaving;
        $data['totalSavings'] = $monthlySaving->totalBalance();

        $longTerm = new LongTerm;
        $data['totalLTL'] = $longTerm->totalBalance();

        $shortTerm = new ShortTerm;
        $data['totalSTL'] = $shortTerm->totalBalance();

        $commodity = new Commodity;
        $data['totalCommodity'] = $commodity->totalBalance();

        $centers = Center::pluck('name', 'id');
        
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
        


        // number of members by center chart
        $membersByCenterData = Member::with('member_pay_point')->get();
        $membersByCenterData = $membersByCenterData->groupBy('pay_point')
        ->map(function ($item, $key) {
            return ($item->count());
        });

        $membersByCenterData = $membersByCenterData->keyBy(function ($value, $key) use ($centers) {
            if (isset($centers[$key])) {
                return $centers[$key];
            } else {
                return 'OTHERS';
            }
        });

        $data['membersByCenterData'] = $membersByCenterData;


        // SHARE
        $data['shares'] = Share::sum('units');

        return view('home', $data);
    }
}
