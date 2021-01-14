<?php

namespace App\Http\Controllers;

use App\MonthlySaving;
use App\MonthlySavingsPayment;
use App\LongTerm;
use App\LongTermPayment;
use App\LongTermLoanDefault;
use App\ShortTerm;
use App\ShortTermPayment;
use App\ShortTermLoanDefault;
use App\Commodity;
use App\CommodityPayment;
use App\CommodityLoanDefault;
use App\Center;
use App\Report;
use App\Share;

use App\Member;
use Carbon\Carbon;

use Illuminate\Http\Request;

class ReportsController extends Controller
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

    
    function generalReportByPaypoint(Request $request) {

        // dd($request->all());

        $data['centers'] = Center::pluck('name', 'id');
        $data['pay_point'] = $request->pay_point ? : 9;
        $data['date_from'] = Carbon::now()->startOfYear();
        $data['date_to'] = Carbon::now()->endOfYear();
        if ($request->date_from) {    
            $data['date_from'] = $request->date_from;
            $data['date_to'] = $request->date_to;
        }

        $report = new Report;
        $data['membersReports'] = $report->generalReportByPaypoint($data['date_from'], $data['date_to'], $data['pay_point']); 


        session([
            'pay_point'         => $data['pay_point'],
            'date_from'         => $data['date_from'],
            'date_to'           => $data['date_to'],
            'membersReports'    => $data['membersReports'],
        ]);

        return view('reports.generalReportByPaypoint', $data);
    }

    
    function generalReportByPaypointPDF($date_from, $date_to, $pay_point) {

        $data['pay_point'] = session('pay_point');
        $data['date_from'] = session('date_from');
        $data['date_to'] = session('date_to');
        $data['membersReports'] = session('membersReports');
        
        $pdf = \PDF::loadView('pdf.generalReportByPaypoint', $data)->setPaper('a4', 'landscape');

        // remove data from session
        session()->forget(['pay_point', 'date_from', 'date_to', 'membersReports']);

        return $pdf->download('REPORT.pdf');
    }

    /**
     * Generate report for those who have defaulted on their monthly loan payment
     */
    function monthlyDefaults(Request $request) {

        // dd($request->all());

        $data['pay_point'] = $request->pay_point ? : 1;
        $data['centers'] = Center::pluck('name', 'id');
        $data['date_from'] = Carbon::now()->startOfYear();
        $data['date_to'] = Carbon::now()->endOfYear();

        if ($request->date_from) {    
            $data['date_from'] = $request->date_from;
            $data['date_to'] = $request->date_to; 
        }

        $report = new Report;
        $data['membersReports'] = $report->monthlyDefaults($data['date_from'], $data['date_to'], $data['pay_point']);

        return view('reports.monthlyDefaults', $data);

    }

    /**
     * Generate report for those who did not finish paying their loan when due
     */
    function loanDefaults(Request $request) {

        // dd($request->all());

        $data['centers'] = Center::pluck('name', 'id');
        $data['date_from'] = Carbon::now()->startOfYear();
        $data['date_to'] = Carbon::now()->endOfYear();

        // select all members of a pay point
        $members = Member::where('pay_point', $request->pay_point)->where('is_active', 1)->orderBy('full_name')->get();

        $membersReports = [];
        foreach($members as $member) {

            // Long term loan reports
            $LongTermsQuery = LongTerm::query();
            $LongTermsQuery = $LongTermsQuery->where('ippis', $member->ippis);

            if ($request->date_from) {    
                $data['date_from'] = $request->date_from;
                $data['date_to'] = $request->date_to; 
                $LongTermsQuery = $LongTermsQuery->whereBetween('loan_date', [$data['date_from'], $data['date_to']]);
            }
            $LongTerms = $LongTermsQuery->get();

            $LongTermLoans = [];
            $LTLTotalBal = 0;
            foreach ($LongTerms as $LongTerm) {
                if($LongTerm->checkLoanDefault()) {
                    $ltl['LTLAmtLoaned'] = $LongTerm->total_amount;
                    $ltl['LTLLoanDate'] = $LongTerm->loan_date;
                    $ltl['LTLLoanEndDate'] = $LongTerm->loan_end_date;
                    $ltl['LTLBal'] = $LongTerm->payments->last()->bal;
                    $LongTermLoans[] = $ltl;
                }
            }

            // Short term loan reports
            $ShortTermsQuery = ShortTerm::query();
            $ShortTermsQuery = $ShortTermsQuery->where('ippis', $member->ippis);

            if ($request->date_from) {    
                $data['date_from'] = $request->date_from;
                $data['date_to'] = $request->date_to; 
                $ShortTermsQuery = $ShortTermsQuery->whereBetween('loan_date', [$data['date_from'], $data['date_to']]);
            }
            $ShortTerms = $ShortTermsQuery->get();

            $ShortTermLoans = [];
            $STLTotalBal = 0;
            foreach ($ShortTerms as $ShortTerm) {
                if($ShortTerm->checkLoanDefault()) {
                    $stl['STLAmtLoaned'] = $ShortTerm->total_amount;
                    $stl['STLLoanDate'] = $ShortTerm->loan_date;
                    $stl['STLLoanEndDate'] = $ShortTerm->loan_end_date;
                    $stl['STLBal'] = $ShortTerm->payments->last()->bal;
                    $ShortTermLoans[] = $stl;
                }
            }

            // Commodities loan reports
            $CommoditiesQuery = Commodity::query();
            $CommoditiesQuery = $CommoditiesQuery->where('ippis', $member->ippis);

            if ($request->date_from) {          
                $data['date_from'] = $request->date_from;
                $data['date_to'] = $request->date_to; 
                $CommoditiesQuery = $CommoditiesQuery->whereBetween('loan_date', [$data['date_from'], $data['date_to']]);
            }
            $Commodities = $CommoditiesQuery->get();

            $CommodityLoans = [];
            $COMMTotalBal = 0;
            foreach ($Commodities as $Commodity) {
                if($Commodity->checkLoanDefault()) {
                    $comm['COMMAmtLoaned'] = $Commodity->total_amount;
                    $comm['COMMLoanDate'] = $Commodity->loan_date;
                    $comm['COMMLoanEndDate'] = $Commodity->loan_end_date;
                    $comm['COMMBal'] = $Commodity->payments->last()->bal;
                    $CommodityLoans[] = $comm;
                }
            }

            if(!empty($LongTermLoans) || !empty($ShortTermLoans) || !empty($CommodityLoans)) {
                $membersReports[] = ['member' => $member, 'LongTermLoans' => $LongTermLoans, 'ShortTermLoans' => $ShortTermLoans, 'CommodityLoans' => $CommodityLoans];
            }
            
        }
        // dd($membersReports);

        $data['membersReports'] = $membersReports; 

        return view('reports.loanDefaults', $data);
    }


    function reportByAccounts() {

		$data['centers'] = Center::pluck('name', 'id');
		$pay_point = request('pay_point') ? : 9;

		$dateFrom = Carbon::now()->startOfMonth();
        $dateTo = Carbon::now()->endOfMonth();
        
        $account_type = 'monthly_savings';
        $data['accountTypes'] = [
            'monthly_savings' => 'Monthly Savings',
            'long_term_loans' => 'Long Term Loans',
            'short_term_loans' => 'Short Term Loans',
            'commodity_loans' => 'Commodity Loans',
        ];

		// SET FILTER DATE IF AVAILABLE
		if (request('dateFrom')) {
			$dateFrom = request('dateFrom');
			$dateTo = request('dateTo');
		}

		// FILTER BY PAY POINT IF AVAILABLE
		if (request('pay_point')) {
			$pay_point = request('pay_point');
		}

		// FILTER BY ACCOUNT TYPE IF AVAILABLE
		if (request('account_type')) {
			$account_type = request('account_type');
		}


        $account_type = request('account_type');

        switch ($account_type) {

        case "monthly_savings":

            $query = MonthlySavingsPayment::query();
            $query = $query->whereBetween('deposit_date', [$dateFrom, $dateTo])->where('is_authorized', 1)->orderBy('id', 'desc')->distinct();
            $query->whereHas('member', function ($q) use($pay_point) {
                    $q->where('pay_point', $pay_point);
            });
            // dd($query->get()->take(10));

            break;
        case "long_term_loans":

            $query = LongTermPayment::query();
            $query = $query->whereBetween('loan_date', [$dateFrom, $dateTo])->where('is_authorized', 1)->orderBy('id', 'desc');
            // $query->whereHas('member', function ($q) use($pay_point) {
            //         $q->where('pay_point', $pay_point);
            // });
            dd($query->get());

            break;
        case "short_term_loans":

            $query = ShortTermPayment::query();
            $query = $query->whereBetween('loan_date', [$dateFrom, $dateTo])->where('is_authorized', 1)->orderBy('id', 'desc');
            $query->whereHas('member', function ($q) use($pay_point) {
                    $q->where('pay_point', $pay_point);
            });
            break;
        case "commodity_loans":

            $query = CommodityPayment::query();
            $query = $query->whereBetween('loan_date', [$dateFrom, $dateTo])->where('is_authorized', 1)->orderBy('id', 'desc');
            $query->whereHas('member', function ($q) use($pay_point) {
                    $q->where('pay_point', $pay_point);
            });
            break;
        default:
            echo "Your favorite color is neither red, blue, nor green!";
        }


        $data['results'] = $query->get();
        $data['account_type'] = $account_type;
        $data['pay_point'] = $pay_point;
        $data['dateFrom'] = $dateFrom;
        $data['dateTo'] = $dateTo;

        return view('reports.accounts', $data);
    }
}
