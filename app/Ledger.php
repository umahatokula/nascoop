<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Ledger;
use App\Center;
use App\Member;
use App\MonthlySaving;
use App\MonthlySavingsPayment;
use App\LongTerm;
use App\LongTermPayment;
use App\ShortTerm;
use App\ShortTermPayment;
use App\Commodity;
use App\CommodityPayment;
use App\LongTermLoanDefault;
use App\ShortTermLoanDefault;
use App\CommodityLoanDefault;
use Carbon\Carbon;
use App\Jobs\ActivityJob;
use App\Events\TransactionOccured;
use App\TransactionType_Ext;
use App\Ledger_Internal;
use App\ActivityLog;
use App\IppisReconciledData;
use App\IppisTrxn;
use App\IppisTrxnPayment;

// define('MIN_SAVINGS', 2000);
define('LONG_TERM_PENALTY_PERCENTAGE', 0.00);
define('SHORT_TERM_PENALTY_PERCENTAGE', 0.00);
define('COMMODITY_PENALTY_PERCENTAGE', 0.00);
define('NUMBER_OF_DAYS_TO_START_REPAYING_LOAN', 30); // Start repaying loan after this number of days

class Ledger extends Model
{

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected $dates = ['date', 'loan_date', 'deposit_date', 'withdrawal_date', 'ippis_deduction_date'];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }


    /**
     * Generata an entry code that will be used for all entries in ledger, ltl payments, stl payments and commodities payments
     */
    function generateTrxnNumber() {

        // The length we want the unique reference number to be
        $trxnNumber_length = 20;

        // A true/false variable that lets us know if we've found a unique reference number or not
        $trxnNumber_found = false;

        // Define possible characters. Characters that may be confused such as the letter 'O' and the number zero aren't included
        $possible_chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";

        // Until we find a unique reference, keep generating new ones
        while (!$trxnNumber_found) {

        // Start with a blank reference number
        $trxnNumber = "";

        // Set up a counter to keep track of how many characters have currently been added
        $i = 0;

        // Add random characters from $possible_chars to $trxnNumber until $trxnNumber_length is reached
        while ($i < $trxnNumber_length) {

            // Pick a random character from the $possible_chars list
            $char = substr($possible_chars, mt_rand(0, strlen($possible_chars)-1), 1);

            $trxnNumber .= $char;

            $i++;

        }

        // Our new unique reference number is generated. Lets check if it exists or not
        $result = Ledger::where('trxn_number', $trxnNumber)->first();

        if (is_null($result)) {

            // We've found a unique number. Lets set the $trxnNumber_found variable to true and exit the while loop
            $trxnNumber_found = true;

        }

        return $trxnNumber;

        }
        
    }


    /**
     * Generate total deductions due for a memeber
     */
    public static function getMemberTotalMonthlyDeduction($ippis, $deduction_for, $done_by) {

        $member = Member::where('ippis', $ippis)->first();
        $oBDeduction_for = Carbon::parse($deduction_for);
        $deductionMatureDays = $oBDeduction_for->subDays(NUMBER_OF_DAYS_TO_START_REPAYING_LOAN);
        $oBDeduction_for = $oBDeduction_for->addDays(NUMBER_OF_DAYS_TO_START_REPAYING_LOAN); //restore the original date before the subtraction above

        if($member) {

            $monthly_savings = $member->latest_monthly_saving();
            $monthly_savings_payment = $member->latest_monthly_savings_payment();

            $long_term = LongTerm::where('ippis', $ippis)->latest('id')->first();
            $long_term_payment = $member->latest_long_term_payment();

            $short_term = ShortTerm::where('ippis', $ippis)->latest('id')->first();
            $short_term_payment = $member->latest_short_term_payment();
            
            $commodity = Commodity::where('ippis', $ippis)->latest('id')->first();
            $commodity_payment = $member->latest_commodities_payment();


            if (!$monthly_savings) {
                $monthly_savings_amount = 2000;
            } else {
                if(!$monthly_savings->amount) {
                    $monthly_savings_amount = 2000;
                } else {
                    $monthly_savings_amount = $monthly_savings->amount;
                }
            }


            // If loan date month is same as deduction month, dont apply repayment
            $ltl_loan_date = $member->latest_long_term_loan() ? $member->latest_long_term_loan()->loan_date : false;
            // $isDueForRepaymentLTL = $ltl_loan_date ? (!$ltl_loan_date->isSameMonth($oBDeduction_for)) : false;
            // $isDueForRepaymentLTL = $ltl_loan_date ? ($ltl_loan_date->lessThan($deductionMatureDays)) : false;

            if($ltl_loan_date) {
                $lenght = $ltl_loan_date->diffInDays($oBDeduction_for);
                $isDueForRepaymentLTL = $lenght > NUMBER_OF_DAYS_TO_START_REPAYING_LOAN && $oBDeduction_for->greaterThan($ltl_loan_date) ? true : false;
            } else {
                $isDueForRepaymentLTL = false;
            }

            if ($isDueForRepaymentLTL):
                if (!$long_term) { // no ltl
                    $long_term_monthly_amount = 0;
                } else { // if there is ltl
                    if(!$long_term->monthly_amount) { // there is NO ltl monthly amt
                        $long_term_monthly_amount = 0;
                    } else { // there is ltl monthly amt
                        if($long_term_payment) {
                            if ($long_term_payment->bal == 0) { // ltl has been fully paid
                                $long_term_monthly_amount = 0;
                            } else {  // ltl has NOT been fully paid
                                if ($long_term_payment->bal < $long_term->monthly_amount) // is ltl balance less than monthly amt?
                                {
                                    $long_term_monthly_amount = $long_term_payment->bal;
                                } else {
                                    $long_term_monthly_amount = $long_term->monthly_amount;
                                }
                            }
                        } else {
                            $long_term_monthly_amount = 0;
                        }
                        
                    }
                }
            else:
                $long_term_monthly_amount = 0;
            endif;


            // If loan date month is same as deduction month, dont apply repayment
            $stl_loan_date = $member->latest_short_term_loan() ? $member->latest_short_term_loan()->loan_date : false;

            // $isDueForRepaymentSTL = $stl_loan_date ? (!$stl_loan_date->isSameMonth($oBDeduction_for)) : false;
            // $isDueForRepaymentSTL = $stl_loan_date ? ($stl_loan_date->lessThan($deductionMatureDays)) : false;

            if($stl_loan_date) {
                $lenght = $stl_loan_date->diffInDays($oBDeduction_for);
                $isDueForRepaymentSTL = $lenght > NUMBER_OF_DAYS_TO_START_REPAYING_LOAN && $oBDeduction_for->greaterThan($stl_loan_date) ? true : false;
            } else {
                $isDueForRepaymentSTL = false;
            }

            if ($isDueForRepaymentSTL):
                if (!$short_term) { // no stl
                    $short_term_monthly_amount = 0;
                } else {// if there is stl
                    if(!$short_term->monthly_amount) { // there is NO stl monthly amt
                        $short_term_monthly_amount = 0;
                    } else { // there is stl monthly amt
                        if($short_term_payment) {
                            if ($short_term_payment->bal == 0) { // stl has been fully paid
                                $short_term_monthly_amount = 0;
                            } else {  // stl has NOT been fully paid
                                if ($short_term_payment->bal < $short_term->monthly_amount) // is stl balance less than monthly amt?
                                {
                                    $short_term_monthly_amount = $short_term_payment->bal;
                                } else {
                                    $short_term_monthly_amount = $short_term->monthly_amount;
                                }
                            }
                        } else {
                            $short_term_monthly_amount = 0;
                        }
                        
                    }
                }
            else:
                $short_term_monthly_amount = 0;
            endif;


            // If loan date month is same as deduction month, dont apply default
            $coml_loan_date = $member->latest_commodity_loan() ? $member->latest_commodity_loan()->loan_date : false;
            // $isDueToPenalizeCOMM = $coml_loan_date ? (!$coml_loan_date->isSameMonth($oBDeduction_for)) : false;
            // $isDueToPenalizeCOMM = $coml_loan_date ? ($coml_loan_date->lessThan($deductionMatureDays)) : false;

            if($coml_loan_date) {
                $lenght = $coml_loan_date->diffInDays($oBDeduction_for);
                $isDueToPenalizeCOMM = $lenght > NUMBER_OF_DAYS_TO_START_REPAYING_LOAN && $oBDeduction_for->greaterThan($coml_loan_date) ? true : false;
            } else {
                $isDueToPenalizeCOMM = false;
            }

            if ($isDueToPenalizeCOMM):
                if (!$commodity) { // no stl
                    $commodity_monthly_amount = 0;
                } else { // if there is stl
                    if(!$commodity->monthly_amount) { // there is NO stl monthly amt
                        $commodity_monthly_amount = 0;
                    } else { // there is stl monthly amt
                        if($commodity_payment) {
                            if ($commodity_payment->bal == 0) { // stl has been fully paid
                                $commodity_monthly_amount = 0;
                            } else { // stl has NOT been fully paid
                                if ($commodity_payment->bal < $commodity->monthly_amount) // is ltl balance less than monthly amt?
                                {
                                    $commodity_monthly_amount = $commodity_payment->bal;
                                } else {
                                    $commodity_monthly_amount = $commodity->monthly_amount;
                                }
                            }
                        } else {
                            $commodity_monthly_amount = 0;
                        }                        
                    }
                }
            else:
                $commodity_monthly_amount = 0;
            endif;

            $result = [
                'ippis'                     => $ippis,
                'full_name'                 => isset($member) ? $member->full_name : '',
                'pay_point'                 => isset($member) ? $member->member_pay_point->name : '',
                'monthly_savings_amount'    => $monthly_savings_amount,
                'long_term_monthly_amount'  => $long_term_monthly_amount,
                'short_term_monthly_amount' => $short_term_monthly_amount,
                'commodity_monthly_amount'  => $commodity_monthly_amount,
                'total'                     => $monthly_savings_amount + $long_term_monthly_amount + $short_term_monthly_amount + $commodity_monthly_amount,
                'deduction_for'             => $deduction_for,
                'month'                     => Carbon::parse($deduction_for)->format('m'),
                'year'                      => Carbon::parse($deduction_for)->format('Y'),
                'done_by'                   => $done_by,
                'created_at'                => Carbon::now(),
                'updated_at'                => Carbon::now(),
            ];

            return $result;
        }

        return false;
                
    }


    /**
     * Execute deductions sent back by IPPIS
     */
    public static function executeDeductions($ippis, $amountDeducted, Array $monthlyDeductions, $ref, $deduction_for, $done_by) {

        $member = Member::where('ippis', $ippis)->first();
        $oBDeduction_for = Carbon::parse($deduction_for);
        // dd($deduction_for);

        if($member) {
            // Check month and year to avoid repeating already executed deduction for a member             
            $deductionAlreadyDone = IppisReconciledData::where('ippis', $ippis)
            ->where('is_successful', 1)
            ->where('month', $oBDeduction_for->format('m'))
            ->where('year', $oBDeduction_for->format('Y'))
            ->first();

            if(is_null($deductionAlreadyDone)) {
                $expectedLTLAmount  = $monthlyDeductions['long_term_monthly_amount'];
                $expectedSTLAmount  = $monthlyDeductions['short_term_monthly_amount'];
                $expectedCOMMAmount = $monthlyDeductions['commodity_monthly_amount'];

                $remittedSavingsAmount = 0;
                $remittedLTLAmount     = 0;
                $remittedSTLAmount     = 0;
                $remittedCOMMAmount    = 0;

                // default amounts
                $longTermDefaultAmt  = 0;
                $shortTermDefaultAmt = 0;
                $commodityDefaultAmt = 0;

                // generate a code to be used for this ledger entry
                $ledger = new Ledger;
                $trxnNumber = $ledger->generateTrxnNumber();

                $longTermDefault  = false;
                $shortTermDefault = false;
                $commodityDefault = false;

                if($amountDeducted != 0) {

                    if ($amountDeducted >= 2000) {
                        $remittedSavingsAmount += 2000;
                        $amountDeducted = $amountDeducted - 2000;
                    } else {
                        $remittedSavingsAmount += $amountDeducted;
                        $amountDeducted = $amountDeducted - $amountDeducted;
                    }

                    if($amountDeducted >= $expectedLTLAmount) {
                        $remittedLTLAmount += $expectedLTLAmount;
                        $amountDeducted = $amountDeducted - $expectedLTLAmount;
                    } else {

                        $longTermDefault =  true;
                        $longTermDefaultAmt =  ($expectedLTLAmount - $amountDeducted) * LONG_TERM_PENALTY_PERCENTAGE;

                        $remittedLTLAmount += $amountDeducted;
                        $amountDeducted = $amountDeducted - $amountDeducted;
                    }

                    if($amountDeducted >= $expectedSTLAmount) {
                        $remittedSTLAmount += $expectedSTLAmount;
                        $amountDeducted = $amountDeducted - $expectedSTLAmount;
                    } else {

                        $shortTermDefault =  true;
                        $shortTermDefaultAmt =  ($expectedSTLAmount - $amountDeducted) * SHORT_TERM_PENALTY_PERCENTAGE;

                        $remittedSTLAmount += $amountDeducted;
                        $amountDeducted = $amountDeducted - $amountDeducted;
                    }

                    if($amountDeducted >= $expectedCOMMAmount) {
                        $remittedCOMMAmount += $expectedCOMMAmount;
                        $amountDeducted = $amountDeducted - $expectedCOMMAmount;
                    } else {

                        $commodityDefault =  true;
                        $commodityDefaultAmt =  ($expectedCOMMAmount - $amountDeducted) * COMMODITY_PENALTY_PERCENTAGE;
                        
                        $remittedCOMMAmount += $amountDeducted;
                        $amountDeducted = $amountDeducted - $amountDeducted;
                    }


                    $remittedSavingsAmount += $amountDeducted;
                }

                $ltlDefaultAmountToDisplay = $expectedLTLAmount - $remittedLTLAmount;
                $stlDefaultAmountToDisplay = $expectedSTLAmount - $remittedSTLAmount;
                $comlDefaultAmountToDisplay = $expectedCOMMAmount - $remittedCOMMAmount;

                // MONTHLY SAVINGS
                $savings_bal = $member->latest_monthly_savings_payment() ? $member->latest_monthly_savings_payment()->bal + $remittedSavingsAmount : $remittedSavingsAmount;

                // make entry in monthly savings payments table
                $MonthlySavingsPayment = [
                    'monthly_saving_id' => $member->latest_monthly_saving() ? $member->latest_monthly_saving()->id : 0,
                    'trxn_number'       => $trxnNumber,
                    'is_authorized'     => 1,
                    'ippis'             => $ippis,
                    'pay_point'         => $member->pay_point,
                    'ref'               => $ref,
                    'deposit_date'      => $deduction_for,
                    'dr'                => 0.00,
                    'cr'                => $remittedSavingsAmount,
                    'bal'               => $savings_bal,
                    'month'             => Carbon::today()->format('m'),
                    'year'              => Carbon::today()->format('Y'),
                    'done_by'           => $done_by,
                    'created_at'        => Carbon::now()->format('Y-m-d h:s:i'),
                ];

                // Trigger event to save trxn in DB as deposit
                // if($remittedSavingsAmount != 0) {
                //     $ledgerInternal = new Ledger_Internal;
                //     $ledgerInternal->recordDepositToSavings($member, $remittedSavingsAmount, $member->ippis.' deposit authorized');
                // }

                $activityLog = new ActivityLog;
                $activityLog->logThis($trxnNumber, $member->ippis, '(MS IPPIS Deduction) '.$ref,$remittedSavingsAmount, 1, $done_by);



                // LONG TERM LOAN
                $long_term_bal = $member->latest_long_term_payment() ? $member->latest_long_term_payment()->bal -$remittedLTLAmount : 0 - $remittedLTLAmount;

                // make entry in long term payments table
                $LongTermPayment = [
                    'long_term_id'      => $member->latest_long_term_loan() ? $member->latest_long_term_loan()->id : 0,
                    'trxn_number'        => $trxnNumber,
                    'is_authorized'     => 1,
                    'ippis'             => $ippis,
                    'pay_point'         => $member->pay_point,
                    'ref'               => $ref,
                    'deposit_date'      => $deduction_for,
                    'dr'                => 0.00,
                    'cr'                => $remittedLTLAmount,
                    'bal'               => $long_term_bal,
                    'month'             => Carbon::today()->format('m'),
                    'year'              => Carbon::today()->format('Y'),
                    'done_by'           => $done_by,
                    'created_at'        => Carbon::now()->format('Y-m-d h:s:i'),
                ];

                // Save trxn in Db as repayment via deposit
                // if($remittedLTLAmount != 0) {
                //     $ledgerInternal = new Ledger_Internal;
                //     $ledgerInternal->recordLTLRepaymentViaDeposit($member, $remittedLTLAmount, $member->ippis.' LTL Repay. Dep. (IPPIS Upload)');
                // }

                $activityLog = new ActivityLog;
                $activityLog->logThis($trxnNumber, $member->ippis, '(LTL IPPIS Deduction) '.$ref, $remittedLTLAmount, 1, $done_by);


                /**
                 * Check if it is ripe for default charge to start applying using NUMBER_OF_DAYS_TO_START_REPAYING_LOAN
                 */
                if($member->latest_long_term_loan()) {
                    
                    // Only treat as default if loan date is 3 or more months greater than deduction date
                    $ltl_loan_date = $member->latest_long_term_loan() ? $member->latest_long_term_loan()->loan_date : false;
                    if($ltl_loan_date) {
                        $isDueToPenalizeLTL = ($ltl_loan_date->diffInMonths($oBDeduction_for) >= 3) ? true : false;
                    } else {
                        $isDueToPenalizeLTL = false;
                    }

                    // treat defaults
                    if($longTermDefault && $isDueToPenalizeLTL) {
                        $LongTermLoanDefault = [
                            'long_term_id'       => $member->latest_long_term_loan() ? $member->latest_long_term_loan()->id : 0,
                            'ippis'              => $ippis,
                            'pay_point'          => $member->pay_point,
                            'default_charge'     => $longTermDefaultAmt,
                            'monthly_obligation' => $member->latest_long_term_loan() ? $member->latest_long_term_loan()->monthly_amount : 0,
                            'actual_paid'        => $remittedLTLAmount,
                            'default_amount'     => ($expectedLTLAmount),
                            'percentage'         => LONG_TERM_PENALTY_PERCENTAGE,
                            'month'              => Carbon::today()->format('m'),
                            'year'               => Carbon::today()->format('Y'),
                            'done_by'            => $done_by,
                            'created_at'         => Carbon::now()->format('Y-m-d h:s:i'),
                        ];

                        $LongTermPaymentOnDefault = [
                            'long_term_id'  => $member->latest_long_term_loan() ? $member->latest_long_term_loan()->id : 0,
                            'trxn_number'   => $trxnNumber,
                            'is_authorized' => 1,
                            'ippis'         => $ippis,
                            'pay_point'     => $member->pay_point,
                            'ref'           => 'DEFAULT ON: '.($ltlDefaultAmountToDisplay),
                            'deposit_date'  => $deduction_for,
                            'dr'            => $longTermDefaultAmt,
                            'cr'            => 0.00,
                            'bal'           => $long_term_bal + $longTermDefaultAmt,
                            'month'         => Carbon::today()->format('m'),
                            'year'          => Carbon::today()->format('Y'),
                            'done_by'       => $done_by,
                            'created_at'    => Carbon::now()->format('Y-m-d h:s:i'),
                        ];

                        $activityLog = new ActivityLog;
                        $activityLog->logThis($trxnNumber, $member->ippis, '(LTL Default)', $longTermDefaultAmt, 1, $done_by);

                    }
                }


                // SHORT TERM LOAN
                $short_term_bal = $member->latest_short_term_payment() ? $member->latest_short_term_payment()->bal - $remittedSTLAmount : 0 -$remittedSTLAmount;

                // make entry in short term payments table
                $ShortTermPayment = [
                    'short_term_id' => $member->latest_short_term_loan() ? $member->latest_short_term_loan()->id : 0,
                    'trxn_number'   => $trxnNumber,
                    'is_authorized' => 1,
                    'ippis'         => $ippis,
                    'pay_point'     => $member->pay_point,
                    'ref'           => $ref,
                    'deposit_date'  => $deduction_for,
                    'dr'            => 0.00,
                    'cr'            => $remittedSTLAmount,
                    'bal'           => $short_term_bal,
                    'month'         => Carbon::today()->format('m'),
                    'year'          => Carbon::today()->format('Y'),
                    'done_by'       => $done_by,
                    'created_at'    => Carbon::now()->format('Y-m-d h:s:i'),
                ];

                // Save trxn in Db as repayment via deposit
                // if($remittedSTLAmount != 0) {
                //     $ledgerInternal = new Ledger_Internal;
                //     $ledgerInternal->recordSTLRepaymentViaDeposit($member, $remittedSTLAmount, $member->ippis.' STL Repay. Dep. (IPPIS Upload)');                    
                // }

                $activityLog = new ActivityLog;
                $activityLog->logThis($trxnNumber, $member->ippis, '(STL IPPIS Deduction) '.$ref, $remittedSTLAmount, 1, $done_by);


                /**
                 * Check if it is ripe for default charge to start applying using NUMBER_OF_DAYS_TO_START_REPAYING_LOAN
                 */
                if($member->latest_short_term_loan()) {

                    // Only treat as default if loan date is 3 or more months greater than deduction date
                    $stl_loan_date = $member->latest_short_term_loan() ? $member->latest_short_term_loan()->loan_date : false;
                    if($stl_loan_date) {
                        $isDueToPenalizeSTL = ($stl_loan_date->diffInMonths($oBDeduction_for) >= 3) ? true : false;
                    } else {
                        $isDueToPenalizeSTL = false;
                    }

                    // treat defaults
                    if($shortTermDefault && $isDueToPenalizeSTL) {

                        $ShortTermLoanDefault = [
                            'short_term_id'      => $member->latest_short_term_loan() ? $member->latest_short_term_loan()->id : 0,
                            'ippis'              => $ippis,
                            'pay_point'          => $member->pay_point,
                            'default_charge'     => $shortTermDefaultAmt,
                            'monthly_obligation' => $member->latest_short_term_loan() ? $member->latest_short_term_loan()->monthly_amount : 0,
                            'actual_paid'        => $remittedSTLAmount,
                            'default_amount'     => ($expectedSTLAmount),
                            'percentage'         => SHORT_TERM_PENALTY_PERCENTAGE,
                            'month'              => Carbon::today()->format('m'),
                            'year'               => Carbon::today()->format('Y'),
                            'done_by'            => $done_by,
                            'created_at'         => Carbon::now()->format('Y-m-d h:s:i'),
                        ];

                        $ShortTermPaymentOnDefault = [
                            'short_term_id' => $member->latest_short_term_loan() ? $member->latest_short_term_loan()->id : 0,
                            'trxn_number'   => $trxnNumber,
                            'is_authorized' => 1,
                            'ippis'         => $ippis,
                            'pay_point'     => $member->pay_point,
                            'ref'           => 'DEFAULT ON: '.($stlDefaultAmountToDisplay),
                            'deposit_date'  => $deduction_for,
                            'dr'            => $shortTermDefaultAmt,
                            'cr'            => 0.00,
                            'bal'           => $short_term_bal + $shortTermDefaultAmt,
                            'month'         => Carbon::today()->format('m'),
                            'year'          => Carbon::today()->format('Y'),
                            'done_by'       => $done_by,
                            'created_at'    => Carbon::now()->format('Y-m-d h:s:i'),
                        ];

                        $activityLog = new ActivityLog;
                        $activityLog->logThis($trxnNumber, $member->ippis, '(STL Default)', $shortTermDefaultAmt, 1, $done_by);

                    }
                }
                

                // COMMODITY LOAN
                $commodity_bal = $member->latest_commodities_payment() ? $member->latest_commodities_payment()->bal - $remittedCOMMAmount : 0 - $remittedCOMMAmount;

                // make entry in short term payments table
                $CommodityPayment = [
                    'commodity_id'      => $member->latest_commodity_loan() ? $member->latest_commodity_loan()->id : 0,
                    'trxn_number'        => $trxnNumber,
                    'is_authorized'     => 1,
                    'ippis'             => $ippis,
                    'pay_point'         => $member->pay_point,
                    'ref'               => $ref,
                    'deposit_date'      => $deduction_for,
                    'dr'                => 0.00,
                    'cr'                => $remittedCOMMAmount,
                    'bal'               => $commodity_bal,
                    'month'             => Carbon::today()->format('m'),
                    'year'              => Carbon::today()->format('Y'),
                    'done_by'           => $done_by,
                    'created_at'        => Carbon::now()->format('Y-m-d h:s:i'),
                ];

                // Save trxn in Db as repayment via deposit
                // if($remittedCOMMAmount != 0) {
                //     $ledgerInternal = new Ledger_Internal;
                //     $ledgerInternal->recordCOMLRepaymentViaDeposit($member, $remittedCOMMAmount, $member->ippis.' COML Repay. Dep. (IPPIS Upload)');                    
                // }

                $activityLog = new ActivityLog;
                $activityLog->logThis($trxnNumber, $member->ippis, '(COML IPPIS Deduction) '.$ref, $remittedCOMMAmount, 1, $done_by);


                /**
                 * Check if it is ripe for default charge to start applying using NUMBER_OF_DAYS_TO_START_REPAYING_LOAN
                 */
                if($member->latest_commodity_loan()) {
                    // Only treat as default if loan date is 3 or more months greater than deduction date
                    $coml_loan_date = $member->latest_commodity_loan() ? $member->latest_commodity_loan()->loan_date : false;
                    if($coml_loan_date) {
                        $isDueToPenalizeCOMM = ($coml_loan_date->diffInMonths($oBDeduction_for) >= 3) ? true : false;
                    } else {
                        $isDueToPenalizeCOMM = false;
                    }               

                    // treat defaults
                    if($commodityDefault && $isDueToPenalizeCOMM) {

                        $CommodityLoanDefault = [
                            'commodity_id'         => $member->latest_commodity_loan() ? $member->latest_commodity_loan()->id : 0,
                            'ippis'                 => $ippis,
                            'pay_point'             => $member->pay_point,
                            'default_charge'        => $commodityDefaultAmt,
                            'monthly_obligation'    => $member->latest_commodity_loan() ? $member->latest_commodity_loan()->monthly_amount : 0,
                            'actual_paid'           => $remittedCOMMAmount,
                            'default_amount'        => ($expectedCOMMAmount),
                            'percentage'            => COMMODITY_PENALTY_PERCENTAGE,
                            'month'                 => Carbon::today()->format('m'),
                            'year'                  => Carbon::today()->format('Y'),
                            'done_by'               => $done_by,
                            'created_at'            => Carbon::now()->format('Y-m-d h:s:i'),
                        ];

                        $CommodityPaymentOnDefault = [
                            'commodity_id'      => $member->latest_commodity_loan() ? $member->latest_commodity_loan()->id : 0,
                            'trxn_number'        => $trxnNumber,
                            'is_authorized'     => 1,
                            'ippis'             => $ippis,
                            'pay_point'         => $member->pay_point,
                            'ref'               => 'DEFAULT ON: '.($comlDefaultAmountToDisplay),
                            'deposit_date'      => $deduction_for,
                            'dr'                => $commodityDefaultAmt,
                            'cr'                => 0.00,
                            'bal'               => $commodity_bal + $commodityDefaultAmt,
                            'month'             => Carbon::today()->format('m'),
                            'year'              => Carbon::today()->format('Y'),
                            'done_by'           => $done_by,
                            'created_at'        => Carbon::now()->format('Y-m-d h:s:i'),
                        ];
                    
                        $activityLog = new ActivityLog;
                        $activityLog->logThis($trxnNumber, $member->ippis, '(COML Default)', $remittedCOMMAmount, 1, $done_by);

                    } 
                }

                // MAKE ENTRIES IN IPPISTRXN AND IPPISTRXNPAYMENTS TABLE
                $ippisTrxnObj = IppisTrxn::where('center_id', $member->pay_point)
                ->where('month', $oBDeduction_for->format('m'))
                ->where('year', $oBDeduction_for->format('Y'))
                ->first();

                if (!$ippisTrxnObj) {
                    $ippisTrxnObj                = new IppisTrxn;
                    $ippisTrxnObj->trxn_number   = $trxnNumber;
                    $ippisTrxnObj->center_id     = $member->pay_point;
                    $ippisTrxnObj->month         = $oBDeduction_for->format('m');
                    $ippisTrxnObj->year          = $oBDeduction_for->format('Y');
                    $ippisTrxnObj->deduction_for = $deduction_for;
                    $ippisTrxnObj->ms_dr         = $remittedSavingsAmount;
                    $ippisTrxnObj->ms_cr         = 0;
                    $ippisTrxnObj->ms_bal        = $remittedSavingsAmount;
                    $ippisTrxnObj->ltl_dr        = $remittedLTLAmount;
                    $ippisTrxnObj->ltl_cr        = 0;
                    $ippisTrxnObj->ltl_bal       = $remittedLTLAmount;
                    $ippisTrxnObj->stl_dr        = $remittedSTLAmount;
                    $ippisTrxnObj->stl_cr        = 0;
                    $ippisTrxnObj->stl_bal       = $remittedSTLAmount;
                    $ippisTrxnObj->coml_dr       = $remittedCOMMAmount;
                    $ippisTrxnObj->coml_cr       = 0;
                    $ippisTrxnObj->coml_bal      = $remittedCOMMAmount;
                    $ippisTrxnObj->done_by       = $done_by;
                    $ippisTrxnObj->save();
                } else {
                    $ippisTrxnObj->ms_dr    += $remittedSavingsAmount;
                    $ippisTrxnObj->ms_bal   += $remittedSavingsAmount;
                    $ippisTrxnObj->ltl_dr   += $remittedLTLAmount;
                    $ippisTrxnObj->ltl_bal  += $remittedLTLAmount;
                    $ippisTrxnObj->stl_dr   += $remittedSTLAmount;
                    $ippisTrxnObj->stl_bal  += $remittedSTLAmount;
                    $ippisTrxnObj->coml_dr  += $remittedCOMMAmount;
                    $ippisTrxnObj->coml_bal += $remittedCOMMAmount;
                    $ippisTrxnObj->save();
                }

                $ippisTrxnPayment        = IppisTrxnPayment::where('ippis_trxn_id', $ippisTrxnObj->id)->first();
                if (!$ippisTrxnPayment ) {
                    $ippisTrxnPayment = new IppisTrxnPayment;
                    $ippisTrxnPayment->ippis_trxn_id = $ippisTrxnObj->id;
                    $ippisTrxnPayment->trxn_number   = $trxnNumber;
                    $ippisTrxnPayment->center_id     = $member->pay_point;
                    $ippisTrxnPayment->month         = $oBDeduction_for->format('m');
                    $ippisTrxnPayment->year          = $oBDeduction_for->format('Y');
                    $ippisTrxnPayment->deduction_for = $deduction_for;
                    $ippisTrxnPayment->ms_dr         = $remittedSavingsAmount;
                    $ippisTrxnPayment->ms_bal        = $remittedSavingsAmount;
                    $ippisTrxnPayment->ltl_dr        = $remittedLTLAmount;
                    $ippisTrxnPayment->ltl_bal       = $remittedLTLAmount;
                    $ippisTrxnPayment->stl_cr        = $remittedSTLAmount;
                    $ippisTrxnPayment->stl_bal       = $remittedSTLAmount;
                    $ippisTrxnPayment->coml_dr       = $remittedCOMMAmount;
                    $ippisTrxnPayment->coml_bal      = $remittedCOMMAmount;
                    $ippisTrxnPayment->save();
                } else {
                    $ippisTrxnPayment->ippis_trxn_id = $ippisTrxnObj->id;
                    $ippisTrxnPayment->trxn_number   = $trxnNumber;
                    $ippisTrxnPayment->center_id     = $member->pay_point;
                    $ippisTrxnPayment->month         = $oBDeduction_for->format('m');
                    $ippisTrxnPayment->year          = $oBDeduction_for->format('Y');
                    $ippisTrxnPayment->deduction_for = $deduction_for;
                    $ippisTrxnPayment->ms_dr         += $remittedSavingsAmount;
                    $ippisTrxnPayment->ms_bal        += $remittedSavingsAmount;
                    $ippisTrxnPayment->ltl_dr        += $remittedLTLAmount;
                    $ippisTrxnPayment->ltl_bal       += $remittedLTLAmount;
                    $ippisTrxnPayment->stl_cr        += $remittedSTLAmount;
                    $ippisTrxnPayment->stl_bal       += $remittedSTLAmount;
                    $ippisTrxnPayment->coml_dr       += $remittedCOMMAmount;
                    $ippisTrxnPayment->coml_bal      += $remittedCOMMAmount;
                    $ippisTrxnPayment->save();
                }

                // make ledger entry
                $ledgerEntry = new Ledger;
                $ledgerEntry->trxn_number          = $trxnNumber;
                $ledgerEntry->is_authorized        = 1;
                $ledgerEntry->member_id            = $member->id;
                $ledgerEntry->pay_point            = $member->pay_point;
                $ledgerEntry->date                 = $deduction_for;
                $ledgerEntry->ref                  = $ref;
                $ledgerEntry->loan_date            = $deduction_for;
                $ledgerEntry->ippis_deduction_date = $deduction_for;
                $ledgerEntry->deposit_date         = $deduction_for;
                $ledgerEntry->withdrawal_date      = $deduction_for;
                $ledgerEntry->savings_dr           = 0.00;
                $ledgerEntry->savings_cr           = $remittedSavingsAmount;
                $ledgerEntry->savings_bal          = $savings_bal;
                $ledgerEntry->long_term_dr         = 0.00;
                $ledgerEntry->long_term_cr         = $remittedLTLAmount;
                $ledgerEntry->long_term_bal        = $long_term_bal;
                $ledgerEntry->short_term_dr        = 0.00;
                $ledgerEntry->short_term_cr         = $remittedSTLAmount;
                $ledgerEntry->short_term_bal       = $short_term_bal;
                $ledgerEntry->commodity_dr         = 0.00;
                $ledgerEntry->commodity_cr         = $remittedCOMMAmount;
                $ledgerEntry->commodity_bal        = $commodity_bal;
                $ledgerEntry->done_by              = $done_by;
                $ledgerEntry->created_at           = Carbon::now()->format('Y-m-d h:s:i');
                $ledgerEntry->save();

                // do this if there was a default on ltl or stl or com 
                if(($longTermDefault && $isDueToPenalizeLTL) || ($shortTermDefault && $isDueToPenalizeSTL)  || ($commodityDefault && $isDueToPenalizeCOMM)) {

                    Ledger::insert([
                        'trxn_number'           => $trxnNumber,
                        'is_authorized'        => 1,
                        'member_id'             => $member->id,
                        'pay_point'            => $member->pay_point,
                        'date'                 => Carbon::parse($deduction_for),
                        'ref'                  => 'DEFAULT ON LTL: '.$ltlDefaultAmountToDisplay.' , STL: '.$stlDefaultAmountToDisplay. ' , COM: '.$comlDefaultAmountToDisplay,
                        'loan_date'            => $deduction_for,
                        'ippis_deduction_date' => $deduction_for,
                        'deposit_date'         => $deduction_for,
                        'withdrawal_date'      => $deduction_for,
                        'savings_dr'           => 0.00,
                        'savings_cr'           => 0.00,
                        'savings_bal'          => $savings_bal,
                        // 'long_term_dr'      => $longTermDefaultAmt,
                        'long_term_dr' => 0.00,
                        'long_term_cr' => 0.00,
                        // 'long_term_bal'     => $long_term_bal + $longTermDefaultAmt,
                        'long_term_bal' => $long_term_bal,
                        // 'short_term_dr'     => $shortTermDefaultAmt,
                        'short_term_dr' => 0.00,
                        'short_term_cr' => 0.00,
                        // 'short_term_bal'    => $short_term_bal + $shortTermDefaultAmt,
                        'short_term_bal' => $short_term_bal,
                        // 'commodity_dr'      => $commodityDefaultAmt,
                        'commodity_dr' => 0.00,
                        'commodity_cr' => 0.00,
                        // 'commodity_bal'     => $commodity_bal + $commodityDefaultAmt,
                        'commodity_bal' => $commodity_bal,
                        'done_by'       => $done_by,
                        'created_at'    => Carbon::now()->format('Y-m-d h:s:i'),
                    ]);
                }

                $summary = [
                    'ippis'            => $ippis,
                    'name'             => $member->full_name,
                    'expected_savings' => $monthlyDeductions['monthly_savings_amount'],
                    'remitted_savings' => $remittedSavingsAmount,
                    'expected_ltl'     => $expectedLTLAmount,
                    'remitted_ltl'     => $remittedLTLAmount,
                    'expected_stl'     => $expectedSTLAmount,
                    'remitted_stl'     => $remittedSTLAmount,
                    'expected_coml'    => $expectedCOMMAmount,
                    'remitted_coml'    => $remittedCOMMAmount,
                    'message'          => 'Successful',
                    'is_successful'    => 1,
                    'error'            => false,
                ];

                $result = [
                    'MonthlySavingsPayment'     => $MonthlySavingsPayment,
                    'LongTermPayment'           => $LongTermPayment,
                    'LongTermLoanDefault'       => $LongTermLoanDefault ?? null,
                    'LongTermPaymentOnDefault'  => $LongTermPaymentOnDefault ?? null,
                    'ShortTermPayment'          => $ShortTermPayment,
                    'ShortTermLoanDefault'      => $ShortTermLoanDefault ?? null,
                    'ShortTermPaymentOnDefault' => $ShortTermPaymentOnDefault ?? null,
                    'CommodityPayment'          => $CommodityPayment,
                    'CommodityLoanDefault'      => $CommodityLoanDefault ?? null,
                    'CommodityPaymentOnDefault' => $CommodityPaymentOnDefault ?? null,
                    'summary'                   => $summary,
                ];
                // \Log::info($result);

                return $result;                

            } else { // Deduction already done
                return [
                    'ippis'   => $ippis,
                    'message' => 'Deduction already done for '.Carbon::parse($deduction_for)->format('m-Y'),
                    'is_successful'   => 0,
                    'error'          => true,
                ];
            }

        } else { // member was not found
            return [
                'ippis'   => $ippis,
                'message' => 'Member not found in DB',
                'is_successful'   => 0,
                'error'          => true,
            ];
        }

    }

    /**
     * Determine if a member should be charged for defaulting on a repayment
     */
    public function isDueToApplyDefaultCharge($loanType) {
        
        if($loanType) {
            $isDueToPenalizeLTL = $this->member->latest_long_term_loan()->where('loan_date', '<=', $oBDeduction_for->subDays(NUMBER_OF_DAYS_TO_START_REPAYING_LOAN))->first();

            dd($isDueToPenalizeLTL);
        }
    }

    public static function areTherePendingTransaction() {
        $MonthlySavingsPayment = MonthlySavingsPayment::where('start_processing', 1)->where('is_authorized', 0)->count();
        $LongTerm = LongTermPayment::where('is_authorized', 0)->count();
        $ShortTerm = ShortTermPayment::where('is_authorized', 0)->count();
        $Commodity = CommodityPayment::where('is_authorized', 0)->count();
        
        return ($MonthlySavingsPayment + $LongTerm + $ShortTerm + $Commodity) > 0 ? true : false;
    }

}
