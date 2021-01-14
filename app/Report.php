<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{

    function generalReportByPaypoint($date_from, $date_to, $pay_point) {

        // select all members of a pay point
        $members = Member::with('long_term_loans.payments', 'short_term_loans.payments', 'commodities_loans.payments')->where('pay_point', $pay_point)->where('is_active', 1)->orderBy('full_name')->get();

        $membersReports = [];
        foreach($members as $member) {

            $savings = $member->monthly_savings_payments;

            // LONG TERM LOAN REPORTS
            $LongTerms = $member->long_term_loans->filter(function ($longTerm, $key) use($date_from, $date_to) {
                return $longTerm->whereBetween('loan_date', [$date_from, $date_to]);
            });

            $LongTermLoans = [];
            $LTLTotalBal = 0;
            foreach ($LongTerms as $LongTerm) {
                $ltl['LTLAmtLoaned'] = $LongTerm->total_amount;
                $ltl['LTLDuration'] = $LongTerm->no_of_months;
                $ltl['LTLBal'] = $LongTerm->payments->last()->bal;

                $LTLTotalBal += $ltl['LTLBal'];

                $LongTermLoans[] = $ltl;
            }
            

            //  SHORT TERM LOAN REPORTS
            $ShortTerms = $member->short_term_loans->filter(function ($shortTerm, $key) use($date_from, $date_to) {
                return $shortTerm->whereBetween('loan_date', [$date_from, $date_to]);
            });

            $ShortTermLoans = [];
            $STLTotalBal = 0;
            foreach ($ShortTerms as $ShortTerm) {
                $stl['STLAmtLoaned'] = $ShortTerm->total_amount;
                $stl['STLDuration'] = $ShortTerm->no_of_months;
                $stl['STLBal'] = $ShortTerm->payments->last()->bal;

                $STLTotalBal += $stl['STLBal'];

                $ShortTermLoans[] = $stl;
            }

            // COMMODITY TERM LOAN REPORTS
            $Commodities = $member->commodities_loans->filter(function ($commodities, $key) use($date_from, $date_to) {
                return $commodities->whereBetween('loan_date', [$date_from, $date_to]);
            });

            $CommodityLoans = [];
            $COMMTotalBal = 0;
            foreach ($Commodities as $Commodity) {
                $comm['COMMAmtLoaned'] = $Commodity->total_amount;
                $comm['COMMDuration'] = $Commodity->no_of_months;
                $comm['COMMBal'] =  $Commodity->payments->last()->bal;

                $COMMTotalBal += $comm['COMMBal'];

                $CommodityLoans[] = $comm;
            }

            $membersReports[] = ['member' => $member, 'savings' => $savings, 'LongTermLoans' => $LongTermLoans, 'ShortTermLoans' => $ShortTermLoans, 'CommodityLoans' => $CommodityLoans];
        }

        return $membersReports;
    }


    /**
     * Generate report for those who have defaulted on their monthly loan payment
     */
    function monthlyDefaults($date_from, $date_to, $pay_point) {

        // select all members of a pay point
        $members = Member::with('long_term_loans_defaults', 'short_term_loans_defaults', 'commodities_loans_defaults')->where('pay_point', $pay_point)->where('is_active', 1)->orderBy('full_name')->get();

        $membersReports = [];
        foreach($members as $member) {

            // Long term loan reports
            $LongTermLoanDefault = $member->long_term_loans_defaults->filter(function ($longTermDefaults, $key) use($date_from, $date_to) {
                return $longTermDefaults->whereBetween('created_at', [$date_from, $date_to]);
            });

            // Short term loan reports
            $ShortTermLoanDefault = $member->short_term_loans_defaults->filter(function ($shortTermDefaults, $key) use($date_from, $date_to) {
                return $shortTermDefaults->whereBetween('created_at', [$date_from, $date_to]);
            });

            // Commodities loan reports
            $CommodityLoanDefault = $member->commodities_loans_defaults->filter(function ($commodityDefaults, $key) use($date_from, $date_to) {
                return $commodityDefaults->whereBetween('created_at', [$date_from, $date_to]);
            });
            // dd(($LongTermLoanDefault)->isNotEmpty());

            if(($LongTermLoanDefault->isNotEmpty()) || ($ShortTermLoanDefault->isNotEmpty()) || ($CommodityLoanDefault->isNotEmpty())) {
                $membersReports[] = ['member' => $member, 'LongTermLoanDefault' => $LongTermLoanDefault, 'ShortTermLoanDefault' => $ShortTermLoanDefault, 'CommodityLoanDefault' => $CommodityLoanDefault];
            }
            
        }

        return $membersReports;
    }

    /**
     * Generate report for those who did not finish paying their loan when due
     */
    function loanDefaults(Request $request) {
        
    }
}
