<?php

namespace App\Http\Controllers;

use App\Share;
use App\Center;
use App\Ledger;
use App\MonthlySaving;
use App\LongTerm;
use App\Commodity;
use App\ShortTerm;
use Carbon\Carbon;
use App\Ledger_Internal;
use App\LongTermPayment;
use App\CommodityPayment;
use App\ShortTermPayment;
use Illuminate\Http\Request;
use App\MonthlySavingsPayment;

class PendingTransactionsController extends Controller
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

	public function getPendingTransactions() {

		$data['centers'] = Center::pluck('name', 'id');

		
        $bankParents = Ledger_Internal::where('ledger_no', 121000)->first();
		$data['banks'] = $bankParents->getChildren()->pluck('account_name', 'ledger_no');

		// $pay_point = request('pay_point') ? : 9;
		$pay_point = null;

		$dateFrom = Carbon::now()->startOfMonth();
		$dateTo = Carbon::now()->endOfMonth();

		// SET FILTER DATE IF AVAILABLE
		if (request('dateFrom')) {
			$dateFrom = request('dateFrom');
			$dateTo = request('dateTo');
		}

		// FILTER BY PAY POINT IF AVAILABLE

		// dd(request('dateFrom'), request('dateTo'), request('pay_point'));			

		/**
		 * MONTHLY SAVINGS
		 */
		$qM = MonthlySavingsPayment::query();
		$qM = $qM->whereBetween('deposit_date', [$dateFrom, $dateTo])->where('is_authorized', 0);
		if (request('pay_point')) {
			$pay_point = request('pay_point');
			$qM->whereHas('member', function ($query) use ($pay_point) {
					$query->where('pay_point', '=', $pay_point)->where('is_active', '=', 1);
			})->get();
		}
		$data['pendingMonthlyTnxs'] = $qM->with('member')->get();


		/**
		 * lONG TERM LOANS
		 */
		$qL = LongTermPayment::query();
		$qL = $qL->whereBetween('loan_date', [$dateFrom, $dateTo])->where('is_authorized', 0);
		if (request('pay_point')) {
			$pay_point = request('pay_point');
			$qL->whereHas('member', function ($query) use ($pay_point) {
					$query->where('pay_point', '=', $pay_point)->where('is_active', '=', 1);
			})->get();
		}	

		$qL2 = LongTermPayment::query();
		$qL2 = $qL2->whereBetween('deposit_date', [$dateFrom, $dateTo])->where('is_authorized', 0);	
		if (request('pay_point')) {
			$pay_point = request('pay_point');
			$qL2->whereHas('member', function ($query) use ($pay_point) {
					$query->where('pay_point', '=', $pay_point)->where('is_active', '=', 1);
			})->get();
		}	

		$qL = $qL->get();
		$qL2 = $qL2->get();
		$concatinated = $qL->concat($qL2);
		$data['pendingLongTermTnxs'] = $concatinated->sortBy('created_at');

		/**
		 * SHORT TERM LOANS
		 */
		$qS = ShortTermPayment::query();
		$qS = $qS->whereBetween('loan_date', [$dateFrom, $dateTo])->where('is_authorized', 0);
		if (request('pay_point')) {
			$pay_point = request('pay_point');
			$qS->whereHas('member', function ($query) use ($pay_point) {
					$query->where('pay_point', '=', $pay_point)->where('is_active', '=', 1);
			})->get();
		}	

		$qS2 = ShortTermPayment::query();
		$qS2 = $qS2->whereBetween('deposit_date', [$dateFrom, $dateTo])->where('is_authorized', 0);	
		if (request('pay_point')) {
			$pay_point = request('pay_point');
			$qS2->whereHas('member', function ($query) use ($pay_point) {
					$query->where('pay_point', '=', $pay_point)->where('is_active', '=', 1);
			})->get();
		}	

		$qS = $qS->get();
		$qS2 = $qS2->get();
		// dd($qS2);
		$concatinated = $qS->concat($qS2);
		$data['pendingShortTermTnxs'] = $concatinated->sortBy('created_at');


		/**
		 * COMMODITY LOANS
		 */
		$qC = CommodityPayment::query();
		$qC = $qC->whereBetween('loan_date', [$dateFrom, $dateTo])->where('is_authorized', 0);
		if (request('pay_point')) {
			$pay_point = request('pay_point');
			$qC->whereHas('member', function ($query) use ($pay_point) {
					$query->where('pay_point', '=', $pay_point)->where('is_active', '=', 1);
			})->get();
		}	

		$qC2 = CommodityPayment::query();
		$qC2 = $qC2->whereBetween('deposit_date', [$dateFrom, $dateTo])->where('is_authorized', 0);	
		if (request('pay_point')) {
			$pay_point = request('pay_point');
			$qC2->whereHas('member', function ($query) use ($pay_point) {
					$query->where('pay_point', '=', $pay_point)->where('is_active', '=', 1);
			})->get();
		}	

		$qC = $qC->get();
		$qC2 = $qC2->get();
		$concatinated = $qC->concat($qC2);
        $data['pendingCommodityTnxs'] = $concatinated->sortBy('created_at');


		/**
		 * SHARES
		 */
		$qS = Share::query();
		$qS = $qS->whereBetween('date_bought', [$dateFrom, $dateTo])->where('is_authorized', 0);
		if (request('pay_point')) {
			$pay_point = request('pay_point');
			$qS->whereHas('member', function ($query) use ($pay_point) {
					$query->where('pay_point', '=', $pay_point)->where('is_active', '=', 1);
			})->get();
		}	
		$data['pendingSharesTnxs'] = $qS->get();


		$data['dateFrom'] = $dateFrom;
		$data['dateTo'] = $dateTo;
		$data['pay_point'] = $pay_point;


        // Ensure there are no pending trxns
        $data['pendingMonthlySavingsTrxnCount'] = MonthlySavingsPayment::where('is_authorized', 0)->count();
        $data['pendingLtlTrxnCount'] = LongTermPayment::where('is_authorized', 0)->count();
        $data['pendingSltTrxnCount'] = ShortTermPayment::where('is_authorized', 0)->count();
        $data['pendingComlTrxnCount'] = CommodityPayment::where('is_authorized', 0)->count();
        $data['pendingShareCount'] = Share::where('is_authorized', 0)->count();
        // dd($data['pendingMonthlySavingsTrxnCount']);

		return view('pendingTransactions.pending', $data);
    }

    /**
     * Commence the processing of a trxn
     */
    public function startProcessing($id, $type) {

        // LTL
        if ($type == 'ltl') {
            $item = LongTermPayment::find($id);
            $item->start_processing = 1;
            $item->save();

            $parent = $item->longTermLoan;
            $parent->start_processing = 1;
            $parent->save();
            
            return redirect()->back();
        }

        if ($type == 'ltl_Rp_Deposit') {
            $item = LongTermPayment::find($id);
            $item->start_processing = 1;
            $item->save();
            
            return redirect()->back();
        }


        // STL
        if ($type == 'stl') {
            $item = ShortTermPayment::find($id);
            $item->start_processing = 1;
            $item->save();

            $parent = $item->shortTermLoan;
            $parent->start_processing = 1;
            $parent->save();
            
            return redirect()->back();
        }

        if ($type == 'stl_Rp_Deposit') {
            $item = ShortTermPayment::find($id);
            $item->start_processing = 1;
            $item->save();
            
            return redirect()->back();
        }


        // COML
        if ($type == 'coml') {
            $item = CommodityPayment::find($id);
            $item->start_processing = 1;
            $item->save();

            $parent = $item->commodity;
            $parent->start_processing = 1;
            $parent->save();
            
            return redirect()->back();
        }

        if ($type == 'coml_Rp_Deposit') {
            $item = CommodityPayment::find($id);
            $item->start_processing = 1;
            $item->save();
            
            return redirect()->back();
        }


        // SAVINGS
        if ($type == 'savings') {
            $item = MonthlySavingsPayment::find($id);
            $item->start_processing = 1;
            $item->save();

            return redirect()->back();
        }


        // SHARES
        if ($type == 'shares') {
            $item = Share::find($id);
            $item->start_processing = 1;
            $item->save();
            
            return redirect()->back();
        }

    }
    

    /**
     * Authorize or decline a trxn
     */
    public function processApplications($id, $type) {

		$data['centers'] = Center::pluck('name', 'id');
		
        $bankParents = Ledger_Internal::where('ledger_no', 121000)->first();
        $data['banks'] = $bankParents->getChildren()->pluck('account_name', 'ledger_no');
        
        if ($type == 'ltl') {
            $data['loan'] = LongTermPayment::where('id', $id)->with('longTermLoan')->first();
            return view('pendingTransactions.processLTLApplications', $data);
        }
        if ($type == 'stl') {
            $data['loan'] = ShortTermPayment::where('id', $id)->with('shortTermLoan')->first();
            return view('pendingTransactions.processSTLApplications', $data);
        }
        if ($type == 'coml') {
            $data['loan'] = CommodityPayment::where('id', $id)->with('commodity')->first();
            return view('pendingTransactions.processCOMLApplications', $data);
        }
        if ($type == 'savings') {
            $data['savings'] = MonthlySavingsPayment::find($id);
            return view('pendingTransactions.processSavingsApplications', $data);
        }
        if ($type == 'shares') {
            $data['pendingCommodityTnx'] = Share::find($id);
            return view('pendingTransactions.processSharesApplications', $data);
        }
    }
}
