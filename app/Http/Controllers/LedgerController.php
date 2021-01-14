<?php
namespace App\Http\Controllers;

use App\Serialisers\CustomSerialiser;

use App\Imports\LedgerImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MonthlyDeductionsExport;
use App\Exports\ReconciledDeductionsExport;
use App\Exports\LedgerExport;
use Importer;
use Exporter;

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
// use App\Events\LoanAuthorized;
// use App\Events\LoanRepayment;
// use App\Events\DepositToSavings;
// use App\Events\WithdrawalFromSavings;
use App\Events\TransactionOccured;
use App\TransactionType_Ext;
use App\Ledger_Internal;
use App\InitialImport;
use App\IppisDeductionsImport;
use App\InventoryItem;

use Illuminate\Http\Request;
define('MIN_SAVINGS', 2000);

class LedgerController extends Controller
{
    protected $count = 0;
    protected $member = null;

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
     * Import Ledger.
     *
     * @param  \App\Ledger  $ledger
     * @return \Illuminate\Http\Response
     */
    public function getImportInitialLedgerSummary(Ledger $ledger) {
        $data['centers'] = Center::pluck('name', 'id');

        $bankParents = Ledger_Internal::where('ledger_no', 121000)->first();
        $data['banks'] = $bankParents->getChildren()->pluck('account_name', 'ledger_no');

        return view('imports.getImportInitialLedgerSummary', $data);
    }

    /**
     * Import Ledger.
     *
     * @param  \App\Ledger  $ledger
     * @return \Illuminate\Http\Response
     */
    public function postImportInitialLedgerSummary(Request $request, Ledger $ledger) {
        // dd($request->all());
        $settings = ShareSetting::first();
        
        // Ensure shares settings are set up
        if (!$settings) {
            flash('Please setup shares settings first')->warning();
            return redirect()->back();
        }

        $rules = [
            'file' => 'required',
        ];

        $messages = [
            'file.required' => 'Please select a file',
        ];

        $this->validate($request, $rules, $messages);

        $excel = Importer::make('Excel');
        $excel->load(request()->file('file'));
        $rows = $excel->getCollection();

        // remove hearder
        unset($rows[0]);

        $data = [];
        foreach($rows as $row) {
            // dd($row);

            $ippis                  = $row[0];
            $lname                  = ($row[1]);
            $fname                  = ($row[2]);
            $sharesAmount           = floatVal($row[3]);
            $monthlyContribution    = floatVal($row[4]);
            $totalSavings           = floatVal($row[5]);
            $ltlDate                = NULL;
            $ltlAmount              = floatVal($row[6]);
            $ltlDuration            = intVal($row[7]);
            $ltlBal                 = floatVal($row[8]);
            $stlDate                = NULL;
            $stlAmount              = floatVal($row[9]);
            $stlDuration            = intVal($row[10]);
            $stlBal                 = floatVal($row[11]);
            $comDate                = NULL;
            $comAmount              = floatVal($row[12]);
            $comDuration            = intVal($row[13]);
            $comBal                 = floatVal($row[14]);

            $data[] = [
                $ippis,
                $lname,
                $fname,
                $sharesAmount,
                $monthlyContribution,
                $totalSavings,
                $ltlDate ,
                $ltlAmount,
                $ltlDuration,
                $ltlBal,
                $stlDate,
                $stlAmount,
                $stlDuration,
                $stlBal ,
                $comDate ,
                $comAmount,
                $comDuration,
                $comBal
            ];
        }
        // dd($data);

        $import = new InitialImport;
        $import->imports = $data;
        $import->pay_point = $request->pay_point;
        $import->bank = $request->bank;
        $import->upload_date = $request->upload_date;
        $import->save();

        flash(count($rows).' records will be imported')->success();
        return redirect()->back();

    }

    /**
     * Authorize transactions
     */
    public function authorizeTransaction(Request $request) {
        // dd($request->all());

        $trxn_number = $request->trxn_number;
        $type        = $request->trxn_type;
        $status      = $request->action == 'authorize' ? 1 : 2;
        $ippis       = $request->ippis;
        $bank        = $request->bank;
        $amount      = $request->amount;
        $tab         = $request->tab;

        // require that a bank is selected for the following type of trxns
        if (($type == 'wd_S' || $type == 'ltl'|| $type == 'stl' || $type == 'ls' || $type == 'ltl_Rp_Deposit' || $type == 'stl_Rp_Deposit' || $type == 'coml_Rp_Deposit') && $status == 1) {
            $rules = [
                'bank' => 'required',
            ];

            $messages = [
                'bank.required' => 'Please select a bank',
            ];

            $this->validate($request, $rules, $messages);
        }

        // require that there is enough money in the selected bank for the following tyoes of trxns
        if ( ($type == 'wd_S' || $type == 'ltl'|| $type == 'stl' || $type == 'ls') && ($amount > $this->checkAccountBalance($bank)) && $status == 1) {
            flash('Not enough money in the selected bank for this transaction')->error();
            return redirect()->back()->withInput();
        }

        if ($status == 1) {
        // if (1) {
            $member = Member::where('ippis', $ippis)->first();
                
            $ledger = Ledger::where('trxn_number', $trxn_number)->where('trxn_type', $type)->first(); // by adding 'type' to the query, we ensure we are selecting the intended trxns eg ltl, stl, coml, ltl_Rp_Deposit etc and not resultant trxns like repayments if adjustments exist eg ltl_Rp_Savings

            if ($type == 'wd_S' || $type == 'ltl_Rp_Deposit' || $type == 'stl_Rp_Deposit' || $type == 'coml_Rp_Deposit') {
                $ledger = Ledger::where('trxn_number', $trxn_number)->first();
            }

            if ($member && $ledger) {

                $ledger->is_authorized  = 1;

                // to be used by LTL, STL, COML and SAVINGS
                $allLedgerAccounts = Ledger_Internal::all();


                $mp = MonthlySavingsPayment::where('trxn_number', $trxn_number)->first();

                if ($mp) {
                    if($mp->is_authorized == 0) {
                        $mp->is_authorized = 1;

                        if ($mp->cr != 0) { // Authorize credit
                            $mp->bal = ($member->latest_monthly_savings_payment() ? $member->latest_monthly_savings_payment()->bal : 0) + $mp->cr;
                        } else { // Authorize debit                
                            $mp->bal = ($member->latest_monthly_savings_payment() ? $member->latest_monthly_savings_payment()->bal : 0) - $mp->dr;
                        }
                        $ledger->savings_bal    = $mp->bal;
                        $mp->save();

                        $member->savings_locked = 0; // by setting this value to 0, trxns that affect a members savings can now be done
                        $member->save();


                        //=================================THIS PART IS FOR ACCOUNTING======================================
                        if($type == 'dp_S') {

                            $ledgerInternal = new Ledger_Internal;
                            $ledgerInternal->recordDepositToSavings($member, $mp->cr, $member->ippis.' deposit authorized', $bank);
                        }

                        if($type == 'wd_S') {

                            $ledgerInternal = new Ledger_Internal;
                            $ledgerInternal->recordWithdrawalFromSavings($member, $mp->net_payment, $member->ippis.' Withdrawal', $bank);

                            if ($mp->processing_fee > 0) {
                                $ledgerInternal->recordSavingsWithdrawalProcessingFee($member, $mp->processing_fee, $member->ippis.' processing fee', $bank);
                            }

                            if ($mp->interest > 0) {
                                $ledgerInternal->recordSavingsWithdrawalInterest($member, $mp->interest, $member->ippis.' interest on w/d from savings', $bank);
                            }

                            if ($mp->bank_charges > 0) {
                                $ledgerInternal->recordSavingsWithdrawalBankTransferCharge($member, $mp->bank_charges, $member->ippis.' Bank transfer charge', $bank);
                            }

                        }
                    }
                }


                // ================================LONG TERM=================================
                $lp = null;
                if($type == 'ltl_Rp_Deposit') {
                    $lp = LongTermPayment::where('trxn_number', $trxn_number)->where('trxn_type', 'ltl_Rp_Deposit')->first();
                }
                if($type == 'ltl') {
                    $lp = LongTermPayment::where('trxn_number', $trxn_number)->where('trxn_type', 'ltl')->first();
                }
                
                if ($lp) {
                    if($lp->is_authorized == 0) {
                        $lp->is_authorized = 1;

                        if ($lp->cr != 0) { // Authorize credit
                            $lp->bal = ($member->latest_long_term_payment() ? $member->latest_long_term_payment()->bal : 0) - $lp->cr;
                        } else { // Authorize debit
                            $lp->bal = ($member->latest_long_term_payment() ? $member->latest_long_term_payment()->bal : 0) + $lp->dr;
                        }


                        if($type == 'ltl') {
                            
                            // deduct adjustment amount from resulting balance as adjustment amount has already been recorded
                            if($lp->longTermLoan->adjustment > 0) {
                                $lp->bal = $lp->bal - $lp->longTermLoan->adjustment;
                            }

                            $ledgerInternal = new Ledger_Internal;
                            $ledgerInternal->recordLTL($member, $lp->longTermLoan->net_payment, $member->ippis.' LTL', $bank);

                            if($lp->longTermLoan->adjustment > 0) {
                                $ledgerInternal->recordLTLAdjustment($member, $lp->longTermLoan->adjustment, $member->ippis.' LTL Adjustment', $bank);
                            }

                            if ($lp->longTermLoan->processing_fee > 0) {
                                $ledgerInternal->recordLTLProcessingFee($member, $lp->longTermLoan->processing_fee, $member->ippis.' LTL processing fee', $bank);
                            }

                            if ($lp->longTermLoan->interest > 0) {
                                $ledgerInternal->recordLTLInterest($member, $lp->longTermLoan->interest, $member->ippis.' LTL interest', $bank);
                            }

                            if ($lp->longTermLoan->bank_charges > 0) {
                                $ledgerInternal->recordLTLBankTransferCharge($member, $lp->longTermLoan->bank_charges, $member->ippis.' LTL Bank transfer charge', $bank);
                            }

                        }

                        $ledger->long_term_bal  = $lp->bal;
                        $lp->save();

                        $longTerm = LongTerm::find($lp->long_term_id);
                        $longTerm->is_approved = 1;
                        $longTerm->save();
                        
                        $member->savings_locked = 0; // by setting this value to 0, trxns that affect a members savings can now be done
                        $member->save();


                        if($type == 'ltl_Rp_Deposit') {

                            $ledgerInternal = new Ledger_Internal;
                            $ledgerInternal->recordLTLRepaymentViaDeposit($member, $ledger->long_term_cr, $member->ippis.' LTL Repay. Dep.', $bank);
                        }
                    }
                }


                // ================================SHORT TERM=================================
                $sp = null;
                if($type == 'stl_Rp_Deposit') {
                    $sp = ShortTermPayment::where('trxn_number', $trxn_number)->where('trxn_type', 'stl_Rp_Deposit')->first();
                }
                if($type == 'stl') {
                    $sp = ShortTermPayment::where('trxn_number', $trxn_number)->where('trxn_type', 'stl')->first();
                }

                if ($sp) {
                    if($sp->is_authorized == 0) {
                        $sp->is_authorized = 1;
                        
                        if ($sp->cr != 0) { // Authorize credit
                            $sp->bal = ($member->latest_short_term_payment() ? $member->latest_short_term_payment()->bal : 0) - $sp->cr;
                        } else { // Authorize debit
                            $sp->bal = ($member->latest_short_term_payment() ? $member->latest_short_term_payment()->bal : 0) + $sp->dr;
                        }


                        if($type == 'stl') {
                            // deduct adjustment amount from resulting balance as adjustment amount has already been recorded
                            if($sp->shortTermLoan->adjustment > 0) {
                                $sp->bal = $sp->bal - $sp->shortTermLoan->adjustment;
                            }

                            $ledgerInternal = new Ledger_Internal;
                            $ledgerInternal->recordSTL($member, $sp->shortTermLoan->net_payment, $member->ippis.' STL Loan', $bank);

                            if($sp->shortTermLoan->adjustment > 0) {
                                $ledgerInternal->recordSTLAdjustment($member, $sp->shortTermLoan->adjustment, $member->ippis.' STL Adjustment', $bank);
                            }

                            if ($sp->shortTermLoan->processing_fee > 0) {
                                $ledgerInternal->recordSTLProcessingFee($member, $sp->shortTermLoan->processing_fee, $member->ippis.' STL processing fee', $bank);
                            }

                            if ($sp->shortTermLoan->interest > 0) {
                                $ledgerInternal->recordSTLInterest($member, $sp->shortTermLoan->interest, $member->ippis.' STL interest', $bank);
                            }

                            if ($sp->shortTermLoan->bank_charges > 0) {
                                $ledgerInternal->recordSTLBankTransferCharge($member, $sp->shortTermLoan->bank_charges, $member->ippis.' STL Bank transfer charge', $bank);
                            }

                        }

                        $ledger->short_term_bal = $sp->bal;
                        $sp->save();

                        $shortTerm = ShortTerm::find($sp->short_term_id);
                        $shortTerm->is_approved = 1;
                        $shortTerm->save();

                        $member->savings_locked = 0; // by setting this value to 0, trxns that affect a members savings can now be done
                        $member->save();

                        if($type == 'stl_Rp_Deposit') {

                            $ledgerInternal = new Ledger_Internal;
                            $ledgerInternal->recordSTLRepaymentViaDeposit($member, $ledger->short_term_cr, $member->ippis.' STL Repay. Dep.', $bank);
                        }
                    }
                }



                // ================================COMMODITY=================================
                $cp = null;
                if($type == 'coml_Rp_Deposit') {
                    $cp = CommodityPayment::where('trxn_number', $trxn_number)->where('trxn_type', 'coml_Rp_Deposit')->first();
                }
                if($type == 'coml') {
                    $cp = CommodityPayment::where('trxn_number', $trxn_number)->where('trxn_type', 'coml')->first();
                }

                if ($cp) {
                    if($cp->is_authorized == 0) {
                        $cp->is_authorized = 1;
                        if ($cp->cr != 0) { // Authorizr credit
                            $cp->bal = ($member->latest_commodities_payment() ? $member->latest_commodities_payment()->bal : 0) - $cp->cr;
                        } else { // Authorize debit
                            $cp->bal = ($member->latest_commodities_payment() ? $member->latest_commodities_payment()->bal : 0) + $cp->dr;
                        }

                        $ledger->commodity_bal  = $cp->bal;
                        $cp->save();

                        $commodity = Commodity::find($cp->commodity_id);
                        $commodity->is_approved = 1;
                        $commodity->save();
                        
                        $member->savings_locked = 0; // by setting this value to 0, trxns that affect a members savings can now be done
                        $member->save();

                        if($type == 'coml') {
                            $ledgerInternal = new Ledger_Internal;
                            $ledgerInternal->recordCOML($member, $cp->dr, $member->ippis.' '.$cp->commodity->ref.' COML Loan', $bank);
                            
                        }

                        if($type == 'coml_Rp_Deposit') {
                            $ledgerInternal = new Ledger_Internal;
                            $ledgerInternal->recordCOMLRepaymentViaDeposit($member, $ledger->commodity_cr, $member->ippis.' COML Repay. Dep.', $bank);
                            
                        }
                    }
                }

                
                $ledger->save();
                        
                $member->savings_locked = 0; // by setting this value to 0, trxns that affect a members savings can now be done
                $member->save();

                Ledger::where('trxn_number', $trxn_number)->update(['is_authorized' => 1]);
                MonthlySavingsPayment::where('trxn_number', $trxn_number)->update(['is_authorized' => 1]);
                LongTermPayment::where('trxn_number', $trxn_number)->update(['is_authorized' => 1]);
                ShortTermPayment::where('trxn_number', $trxn_number)->update(['is_authorized' => 1]);
                CommodityPayment::where('trxn_number', $trxn_number)->update(['is_authorized' => 1]);
                ActivityLog::where('trxn_number', $trxn_number)->update(['is_authorized' => 1]);

                ActivityLog::where('trxn_number', $trxn_number)->update(['is_authorized' => 1]);
            }
            

        } else {

            Ledger::where('trxn_number', $trxn_number)->update(['is_authorized' => 2]);
            MonthlySavingsPayment::where('trxn_number', $trxn_number)->update(['is_authorized' => 2]);
            LongTermPayment::where('trxn_number', $trxn_number)->update(['is_authorized' => 2]);
            ShortTermPayment::where('trxn_number', $trxn_number)->update(['is_authorized' => 2]);
            $commodity = CommodityPayment::where('trxn_number', $trxn_number)->update(['is_authorized' => 2]);
            ActivityLog::where('trxn_number', $trxn_number)->update(['is_authorized' => 2]);
                  
            $member = Member::where('ippis', $ippis)->first();
            
            if ($member) {
                $member->savings_locked = 0; // by setting this value to 0, trxns that affect a members savings can now be done
                $member->save();
            }

            if($type == 'coml') {
                $commodityPayment = CommodityPayment::with('commodity')->where('trxn_number', $trxn_number)->first();
                /**
                 * Increment inventory
                 */
                $item = InventoryItem::find($commodityPayment->commodity->inventory_item_id);
                $item->increment('inventory_onhand', $commodityPayment->commodity->quantity);
            }

        }

        return redirect()->back()->with( ['tab' => $tab] );
    }

    /** 
     * Export individual members ledger as excel
     */
    public function memberLedgerExcel($ippis) {
        $member = Member::where('ippis', $ippis)->first();

        Toastr::success('Export successful', 'Success', ["positionClass" => "toast-bottom-right"]);
        return Excel::download(new LedgerExport($ippis), 'LEDGER_'.$member->full_name.'.xlsx');
    }

    /**
     * Export individual members ledger as PDF
     */
    function memberLedgerPdf($ippis, $date_from, $date_to) {
        $member = Member::where('ippis', $ippis)->first();
        $data['member'] = $member;

        $ledgerQuery = Ledger::query();
        $ledgerQuery = $ledgerQuery->where('member_id', $data['member']->id);
 
        $ledgerQuery = $ledgerQuery->whereBetween('date', [$date_from, $date_to]);

        $data['ledgers'] = $ledgerQuery->get();
        
        $pdf = \PDF::loadView('pdf.ledger', $data)->setPaper('a4', 'landscape');
        return $pdf->download('LEDGER_'.$member->full_name.'.pdf');
    }

    /**
     * Print individual members ledger as PDF
     */
    function memberLedgerPrint($ippis, $date_from, $date_to) {
        $member = Member::where('ippis', $ippis)->first();
        $data['member'] = $member;

        $ledgerQuery = Ledger::query();
        $ledgerQuery = $ledgerQuery->where('member_id', $data['member']->id);
 
        $ledgerQuery = $ledgerQuery->whereBetween('date', [$date_from, $date_to]);

        $data['ledgers'] = $ledgerQuery->get();
        
        $pdf = \PDF::loadView('pdf.ledger', $data)->setPaper('a4', 'landscape');
        return $pdf->stream();
    }

    public function checkAccountBalance($bank_code) {
        // dd($request->bank_code);

        $result = [];
        $total = 0;

        $ledger_no = $bank_code;
        $account = Ledger_Internal::where('ledger_no', $ledger_no)->first();

        if($account) {
            if($account->usage == 'detail') {
                $balance = $account->getLedgerAccountBalance();

                $result[] = [$account, $balance];
                $total += $balance;
            }

            if($account->usage == 'header') {
                foreach($account->getAllDescendants() as $child) {
                    $balance = $child->getLedgerAccountBalance();

                    $result[] = [$child, $balance];
                    $total += $balance;
                }
            }
        }
        return $total;
    }
}
