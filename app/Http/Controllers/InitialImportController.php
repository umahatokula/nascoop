<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Ledger;
use App\Center;
use App\Member;
use App\User;
use App\MonthlySaving;
use App\MonthlySavingsPayment;
use App\LongTerm;
use App\LongTermPayment;
use App\ShortTerm;
use App\ShortTermPayment;
use App\Commodity;
use App\CommodityPayment;
use Carbon\Carbon;
use Toastr;
use App\IppisDeduction;
use App\ActivityLog;
use App\Share;
use App\ShareSetting;
use App\Account;
use App\Events\TransactionOccured;
use App\TransactionType_Ext;
use App\Ledger_Internal;
use App\InitialImport;
use Exception;

class InitialImportController extends Controller
{
    

    function doInitialImport(Request $request, Ledger $ledger) {

        $settings = ShareSetting::first();
    
        $tempLog = InitialImport::where('is_done', 0)->first();
		// dd($tempLog->imports);
		
		$Ledger_Internal = new Ledger_Internal;


        if ($tempLog) {
        	
	        $pay_point = $tempLog->pay_point;
			$upload_date = $tempLog->upload_date;
			$bank = $tempLog->bank;
        	
	        foreach($tempLog->imports as $row) {

				// generate a code to be used for this ledger entry
				$trxnNumber = $ledger->generateTrxnNumber();

				$ippis                  = $row[0];
				$lname                  = ($row[1]);
				$fname                  = ($row[2]);
				$sharesAmount           = floatVal($row[3]);
				$monthlyContribution    = floatVal($row[4]);
				$totalSavings           = floatVal($row[5]);
				$ltlDate                = $row[6];
				$ltlAmount              = floatVal($row[7]);
				$ltlDuration            = intVal($row[8]);
				$ltlBal                 = floatVal($row[9]);
				$stlDate                = $row[10];
				$stlAmount              = floatVal($row[11]);
				$stlDuration            = intVal($row[12]);
				$stlBal                 = floatVal($row[13]);
				$comDate                = $row[14];
				$comAmount              = floatVal($row[15]);
				$comDuration            = intVal($row[16]);
				$comBal                 = floatVal($row[17]);

				try {
						if(!empty($ippis) || !empty($monthlyContribution) || !empty($ltlDate) || !empty($ltlAmount) || !empty($ltlBal) || !empty($ltlDuration) || !empty($stlDate) || !empty($stlAmount) || !empty($stlBal) || !empty($stlDuration) || !empty($comDate) || !empty($comAmount) || !empty($comBal) || !empty($comDuration) || !empty($lname) || !empty($fname)) {

							$member = Member::where('ippis', $ippis)->first();
							
							if ($member) {
									// Member edit
									$member->fname     = trim($fname);
									$member->lname     = trim($lname);
									$member->full_name = trim($fname). ' '.trim($lname);
									$member->ippis     = $ippis;
									$member->username  = $ippis;
									$member->pay_point = $pay_point;
									$member->shares_amount = $sharesAmount;
									$member->is_approved = 1;
									$member->save();

									$shareEntry = Share::where('ippis', $ippis)->first();
									if (!$shareEntry) {
										$share                   = new Share;
										$share->ippis            = $member->ippis;
										$share->date_bought      = $upload_date;;
										$share->units            = $sharesAmount / $settings->rate;
										$share->amount           = $sharesAmount;
										$share->rate_when_bought = $settings->rate;
										$share->trxn_number      = $share->generateTrxnNumber();
										$share->payment_method   = 'savings';
										$share->is_authorized    = 1;
										$share->save();

										// $Ledger_Internal->recordSharesBought($member, $sharesAmount, 'Initial Upload - '.$ippis, $bank);
									}

									$user             = User::where('ippis', $ippis)->first();
									if(!$user ) {
										$user = new User;
									}
									$user->name       = $member->full_name; 
									$user->username   = $ippis;
									$user->ippis      = $ippis;
									$user->password   = \Hash::make($ippis);
									$user->save();
							} else {
									// Member create
									$member                = new Member;
									$member->fname         = trim($fname);
									$member->lname         = trim($lname);
									$member->full_name     = trim($fname). ' '.trim($lname);
									$member->ippis         = $ippis;
									$member->username  	   = $ippis;
									$member->pay_point     = $pay_point;
									$member->shares_amount = $sharesAmount;
									$member->is_approved   = 1;
									$member->save();

									$shareEntry = Share::where('ippis', $ippis)->first();
									if (!$shareEntry) {
										$share                   = new Share;
										$share->ippis            = $member->ippis;
										$share->date_bought      = $upload_date;;
										$share->units            = $sharesAmount / $settings->rate;
										$share->amount           = $sharesAmount;
										$share->rate_when_bought = $settings->rate;
										$share->trxn_number      = $share->generateTrxnNumber();
										$share->payment_method   = 'savings';
										$share->is_authorized    = 1;
										$share->save();

										// $Ledger_Internal->recordSharesBought($member, $sharesAmount, 'Initial Upload - '.$ippis, $bank);
									}

									$user             = new User;
									$user->name       = $member->full_name; 
									$user->username   = $ippis;
									$user->ippis      = $ippis;
									$user->password   = \Hash::make($ippis);
									$user->save();
							}
							
							$ledgerEntry = Ledger::where('member_id', $member->id)->first();
	
							// only make entry if it does not already exist in ledger
							if(!$ledgerEntry) {

								// open an account for this member
								try {

									Account::insert([
											'account_no' => $member->ippis,
											'entity_type' => 'p',
									]);
								} catch (Exception $e) {

								}

								// assign role
								$user->assignRole('member');


								// to be used by LTL, STL, COML and SAVINGS
								$allLedgerAccounts = Ledger_Internal::all();

								// monthly saving
								$monthlySaving          = new MonthlySaving;
								$monthlySaving->ippis   = $ippis;
								$monthlySaving->amount  = $monthlyContribution;
								$monthlySaving->done_by = 17679;
								$monthlySaving->save();

								$monthlySavingsPayment                    = new MonthlySavingsPayment;
								$monthlySavingsPayment->trxn_number       = $trxnNumber;
								$monthlySavingsPayment->is_authorized     = 1;
								$monthlySavingsPayment->ippis             = $ippis;
								$monthlySavingsPayment->pay_point         = $member->pay_point;
								$monthlySavingsPayment->ref               = 'BALANCE BROUGHT FORWARD';
								$monthlySavingsPayment->monthly_saving_id = $monthlySaving->id;
								$monthlySavingsPayment->dr                = 0.00;
								$monthlySavingsPayment->cr                = $totalSavings;
								$monthlySavingsPayment->bal               = $totalSavings;
								$monthlySavingsPayment->deposit_date      = $upload_date;
								$monthlySavingsPayment->month             = Carbon::today()->format('m');
								$monthlySavingsPayment->year              = Carbon::today()->format('Y');
								$monthlySavingsPayment->done_by           = 17679;
								$monthlySavingsPayment->save();

								// Trigger event to save trxn in DB as deposit
								if($totalSavings != 0) {

										$trxnType = TransactionType_Ext::where('xact_type_code_ext', 'dp_S')->first();


								        $centerName = $member->center ? $member->center->name : '';
										$ledger_no = $trxnType->getDetailAccountForThisTransactionType('cr', $centerName) ;
								
										$parent = $allLedgerAccounts->where('ledger_no', $trxnType->associated_trxns['dr'])->first();
										$ledger_no_dr = $member->member_pay_point ? $member->member_pay_point->transacting_bank_ledger_no : $parent->ledger_no;

										$members_savings = $ledger_no;
										$bank = $ledger_no_dr;
								
										// event(new TransactionOccured($members_savings, $bank, microtime(), 'cr', 'dp_S', $member->ippis, 'saving', $totalSavings, 'Initial Upload - '.$ippis));

										// $Ledger_Internal->recordDepositToSavings($member, $totalSavings, 'Initial Upload - '.$ippis, $bank);
										
								}

								// $activityLog = new ActivityLog;
								// $activityLog->logThis($trxnNumber, $member->ippis, 'MS BALANCE BROUGHT FORWARD', $totalSavings, 1, 17679);

								// make entry in long term loan table
								$ltlMonthlyAmount = $ltlDuration != 0 ? $ltlAmount / $ltlDuration : $ltlAmount;

								$ltl = new LongTerm;
								$ltl->pay_point      = $member->pay_point;
								$ltl->ref = 'BALANCE BROUGHT FORWARD';
								if ($ltlDate) {
										$ltlDate            = str_replace(',', ' ', $ltlDate);
										$ltlLoanEndDate     = Carbon::parse($ltlDate)->addMonths($ltlDuration);
										$ltl->loan_date     = $ltlDate;
										$ltl->loan_end_date = $ltlLoanEndDate;
								} else {
										$ltl->loan_date = $upload_date;
								}
								$ltl->ippis          = $ippis;
								$ltl->no_of_months   = $ltlDuration;
								$ltl->total_amount   = $ltlAmount;
								$ltl->monthly_amount = $ltlMonthlyAmount;
								$ltl->done_by        = 17679;
								$ltl->save();


								$longTermPayment                = new LongTermPayment;
								$longTermPayment->trxn_number   = $trxnNumber;
								$longTermPayment->is_authorized = 1;
								$longTermPayment->ippis         = $ippis;
								$longTermPayment->pay_point     = $member->pay_point;
								$longTermPayment->ref           = 'BALANCE BROUGHT FORWARD';
								$longTermPayment->long_term_id  = $ltl->id;
								$longTermPayment->dr            = $ltlBal;
								$longTermPayment->cr            = 0.00;
								$longTermPayment->bal           = $ltlBal;
								$longTermPayment->month         = Carbon::today()->format('m');
								$longTermPayment->year          = Carbon::today()->format('Y');
								$longTermPayment->done_by       = 17679;
								$longTermPayment->loan_date       = $upload_date;
								$longTermPayment->save();

								// fire event to save trxn in ledger transactions
								if($ltlBal != 0) {
									$trxnType = TransactionType_Ext::where('xact_type_code_ext', 'ltl')->first();
									
									$centerName = $member->center ? $member->center->name : '';
									$ledger_no = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName) ;
									
									$parent = $allLedgerAccounts->where('ledger_no', $trxnType->associated_trxns['cr'])->first();
									$ledger_no_dr = $member->member_pay_point ? $member->member_pay_point->transacting_bank_ledger_no : $parent->ledger_no;
									
									$bank = $ledger_no_dr;
									$loan_balance = $ledger_no;
									
									// event(new TransactionOccured($bank, $loan_balance, microtime(), 'cr', 'ltl', $member->ippis, 'ltl', $ltlBal, 'Initial Upload '.$member->ippis));

									// $Ledger_Internal->recordLTL($member, $ltlBal, 'Initial Upload - '.$ippis, $bank);
								}

								// $activityLog = new ActivityLog;
								// $activityLog->logThis($trxnNumber, $member->ippis, 'LTL BALANCE BROUGHT FORWARD', $ltlBal, 1, 17679);

								// make entry in short term loan table
								$stlMonthlyAmount = $stlDuration != 0 ? $stlAmount / $stlDuration : $stlAmount;

								$stl = new ShortTerm;
								$stl->pay_point      = $member->pay_point;
								$stl->ref = 'BALANCE BROUGHT FORWARD';
								if ($stlDate) {
									$stlLoanEndDate     = Carbon::parse($ltlDate)->addMonths($ltlDuration);
									$stl->loan_date     = $stlDate;
									$stl->loan_end_date = $stlLoanEndDate;
								} else {
									$stl->loan_date = $upload_date;
								}
								$stl->ippis          = $ippis;
								$stl->no_of_months   = $stlDuration;
								$stl->total_amount   = $stlAmount;
								$stl->monthly_amount = $stlMonthlyAmount;
								$stl->done_by        = 17679;
								$stl->save();

								$shortTermPayment                = new ShortTermPayment;
								$shortTermPayment->trxn_number   = $trxnNumber;
								$shortTermPayment->is_authorized = 1;
								$shortTermPayment->ippis         = $ippis;
								$shortTermPayment->pay_point     = $member->pay_point;
								$shortTermPayment->ref           = 'BALANCE BROUGHT FORWARD';
								$shortTermPayment->short_term_id = $stl->id;
								$shortTermPayment->dr            = $stlBal;
								$shortTermPayment->cr            = 0.00;
								$shortTermPayment->bal           = $stlBal;
								$shortTermPayment->month         = Carbon::today()->format('m');
								$shortTermPayment->year          = Carbon::today()->format('Y');
								$shortTermPayment->done_by       = 17679;
								$shortTermPayment->loan_date       = $upload_date;
								$shortTermPayment->save(); 

								// fire event to save trxn in ledger transactions
								if($stlBal != 0) {

										$trxnType = TransactionType_Ext::where('xact_type_code_ext', 'stl')->first();
										
								        $centerName = $member->center ? $member->center->name : '';
										$ledger_no = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName) ;

										$parent = $allLedgerAccounts->where('ledger_no', $trxnType->associated_trxns['cr'])->first();
										$ledger_no_dr = $member->member_pay_point ? $member->member_pay_point->transacting_bank_ledger_no : $parent->ledger_no;

										$bank = $ledger_no_dr;
										$loan_balance = $ledger_no;

										// fire event to save trxn in ledger transactions
										// event(new TransactionOccured($bank, $loan_balance, microtime(), 'cr', 'stl', $member->ippis, 'stl', $stlBal, 'Initial Upload '.$member->ippis));

										// $Ledger_Internal->recordSTL($member, $stlBal, 'Initial Upload - '.$ippis, $bank);
								}

								// $activityLog = new ActivityLog;
								// $activityLog->logThis($trxnNumber, $member->ippis, 'STL BALANCE BROUGHT FORWARD', $stlBal, 1, 17679);

								// make entry in commodity loan table
								$comMonthlyAmount = $comDuration != 0 ? $comAmount / $comDuration : $comAmount;

								$commodity = new Commodity;
								$commodity->pay_point      = $member->pay_point;
								$commodity->ref = 'BALANCE BROUGHT FORWARD';
								if ($comDate) {
										$comLoanEndDate = Carbon::parse($comDate)->addMonths($comDuration);
										$commodity->loan_date = $comDate;
										$commodity->loan_end_date = $comLoanEndDate;
								} else {
										$commodity->loan_date = $upload_date;
								}
								$commodity->ippis = $ippis;
								$commodity->no_of_months = $comDuration;
								$commodity->total_amount = $comAmount;
								$commodity->monthly_amount = $comMonthlyAmount;
								$commodity->done_by = 17679;
								$commodity->save();
								
								$commodityPayment = new CommodityPayment;
								$commodityPayment->trxn_number = $trxnNumber;
								$commodityPayment->is_authorized = 1;
								$commodityPayment->ippis = $ippis;
								$commodityPayment->pay_point      = $member->pay_point;
								$commodityPayment->ref = 'BALANCE BROUGHT FORWARD';
								$commodityPayment->commodity_id = $commodity->id;
								$commodityPayment->dr = $comBal;
								$commodityPayment->cr = 0.00;
								$commodityPayment->bal = $comBal;
								$commodityPayment->month = Carbon::today()->format('m');
								$commodityPayment->year = Carbon::today()->format('Y');
								$commodityPayment->done_by = 17679;
								$commodityPayment->loan_date       = $upload_date;
								$commodityPayment->save();

								// fire event to save trxn in ledger transactions
								if($comBal != 0) {
										$trxnType = TransactionType_Ext::where('xact_type_code_ext', 'coml')->first();
										
								        $centerName = $member->center ? $member->center->name : '';
										$ledger_no_dr = $trxnType->getDetailAccountForThisTransactionType('dr', $centerName) ;

										$sales_account = $allLedgerAccounts->where('ledger_no', $trxnType->associated_trxns['cr'])->first();

										$loan_balance = $ledger_no_dr;
										$sales = $sales_account ? $sales_account->ledger_no : '100000';
										// dd($sales, $loan_balance);

										// fire event to save trxn in ledger transactions
										// event(new TransactionOccured($sales, $loan_balance, microtime(), 'cr', 'coml', $member->ippis, 'coml', $comBal, 'Initial Upload '.$member->ippis));

										// $Ledger_Internal->recordCOML($member, $comBal, 'Initial Upload - '.$ippis, $bank);
								}


								// $activityLog = new ActivityLog;
								// $activityLog->logThis($trxnNumber, $member->ippis, 'CL BALANCE BROUGHT FORWARD', $comBal, 1, 17679);

								// LEDGER ENTRY
								$ledger = new Ledger;
								$ledger->trxn_number     = $trxnNumber;
								$ledger->is_authorized  = 1;
								$ledger->member_id       = $member->id;
								$ledger->pay_point      = $member->pay_point;
								$ledger->date           = $upload_date;
								$ledger->ref            = 'BALANCE BROUGHT FORWARD';
								$ledger->savings_dr     = 0.00;
								$ledger->savings_cr     = 0.00;
								$ledger->savings_bal    = $totalSavings;
								$ledger->long_term_dr   = 0.00;
								$ledger->long_term_cr   = 0.00;
								$ledger->long_term_bal  = $ltlBal;
								$ledger->short_term_dr  = 0.00;
								$ledger->short_term_cr  = 0.00;
								$ledger->short_term_bal = $stlBal;
								$ledger->commodity_dr   = 0.00;
								$ledger->commodity_cr   = 0.00;
								$ledger->commodity_bal  = $comBal;
								$ledger->done_by = 17679;
								$ledger->save();
							}

						}
				} catch (App\Exceptions\Handler $exception) {
						dd($e);
						return back()->withError($exception->getMessage())->withInput();
				}

				// cause delay
				usleep(200000);
						
			}

			$tempLog->is_done = 1;
			$tempLog->save();
		}

	}
	
	
}

