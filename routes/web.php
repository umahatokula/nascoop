<?php

use Carbon\Carbon;
use App\Events\LoanAuthorized;
use App\Ledger_Internal;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('dashboard');
});

Auth::routes();

// member
Route::resource('/member', 'MemberController');

// members
Route::get('/members/register', 'MembersController@register')->name('members.register');
Route::post('/members/register/download', 'MembersController@registerDownload')->name('members.register.download');
Route::get('/updateMemberInformation', 'MembersController@updateMemberInformation')->name('members.updateMemberInformation');
Route::post('/postUpdateMemberInformation', 'MembersController@postUpdateMemberInformation')->name('members.postUpdateMemberInformation');
Route::get('/members', 'MembersController@index')->name('members.index');
Route::get('/members/{ippis}/ledger', 'MembersController@ledger')->name('members.ledger');
Route::get('/members/{ippis}/ledger-excel', 'LedgerController@memberLedgerExcel')->name('members.ledgerExcel');
Route::get('/members/{ippis}/{date_from}/{date_to}/ledger-pdf', 'LedgerController@memberLedgerPdf')->name('members.ledgerPdf');
Route::get('/members/{ippis}/{date_from}/{date_to}/ledger-print', 'LedgerController@memberLedgerPrint')->name('members.ledgerPrint');
Route::get('/members/new-member', 'MembersController@addMember')->name('members.addMember');
Route::post('/members/add/save', 'MembersController@saveMember')->name('saveMember');
Route::get('/members/{ippis}', 'MembersController@show')->name('members.show');
Route::get('/members/{ippis}/edit', 'MembersController@editMember')->name('editMember');
Route::put('/members/{ippis}/update', 'MembersController@updateMember')->name('updateMember');
Route::get('/members/{ippis}/dashboard', 'MembersController@dashboard')->name('members.dashboard');
Route::get('/members/dashboard/search', 'MembersController@dashboardSearch')->name('members.dashboardSearch');
Route::get('/members/{ippis}/status', 'MembersController@status')->name('members.status');
Route::get('/members/{ippis}/status/deactivation-summary', 'MembersController@deactivationSummary')->name('members.deactivationSummary');

// monthly savings
Route::get('/members/{withdrawal_id}/withdrawal/payment-voucher/pdf', 'MonthlySavingController@withdrawalPaymentVoucherPDF')->name('members.withdrawalPaymentVoucherPDF');
Route::get('/members/{withdrawal_id}/withdrawal/payment-voucher', 'MonthlySavingController@withdrawalPaymentVoucher')->name('members.withdrawalPaymentVoucher');

Route::get('/members/{ippis}/savings', 'MonthlySavingController@monthlySavings')->name('members.savings');
Route::get('/members/{ippis}/new-savings', 'MonthlySavingController@newSavings')->name('members.newSavings');
Route::post('/members/post-new-savings/{ippis}', 'MonthlySavingController@postNewSavings')->name('members.postNewSavings');

Route::get('/members/{ippis}/savings-change-obligation', 'MonthlySavingController@savingsChangeObligation')->name('members.savingsChangeObligation');
Route::post('/members/post-savings-change-obligation/{ippis}', 'MonthlySavingController@postSavingsChangeObligation')->name('members.postSavingsChangeObligation');

Route::get('/members/{ippis}/savings-withdrawal', 'MonthlySavingController@savingsWithrawal')->name('members.savingsWithrawal');
Route::post('/members/post-savings-withdrawal/{ippis}', 'MonthlySavingController@postSavingsWithrawal')->name('members.postSavingsWithrawal');

// short term loans
Route::get('/members/{short_term_loan_id}/short-term/payment-voucher/pdf', 'ShortTermController@shortTermLoansPaymentVoucherPDF')->name('members.shortTermLoansPaymentVoucherPDF');
Route::get('/members/{short_term_loan_id}/short-term/payment-voucher', 'ShortTermController@shortTermLoansPaymentVoucher')->name('members.shortTermLoansPaymentVoucher');
Route::get('/members/{ippis}/short-term', 'ShortTermController@shortTermLoans')->name('members.shortTermLoans');
Route::get('/members/{ippis}/new-short-loan', 'ShortTermController@newShortLoan')->name('members.newShortLoan');
Route::post('/members/post-new-short-loan/{ippis}', 'ShortTermController@postNewShortLoan')->name('members.postNewShortLoan');
Route::get('/members/short-term-loan-repayment/{ippis}', 'ShortTermController@shortLoanRepayment')->name('members.shortLoanRepayment');
Route::post('/members/short-term-loan-repayment/{ippis}', 'ShortTermController@postShortLoanRepayment')->name('members.postShortLoanRepayment');
Route::get('/members/{loan_id}/short-term-loan-details', 'ShortTermController@loanDetails')->name('members.shortLoanDetails');

// commodities
Route::get('/members/{ippis}/commodity', 'CommodityController@commodity')->name('members.commodity');
Route::get('/members/{ippis}/new-commodity', 'CommodityController@newCommodityLoan')->name('members.newCommodityLoan');
Route::post('/members/post-new-commodity-loan/{ippis}', 'CommodityController@postNewCommodityLoan')->name('members.postNewCommodityLoan');
Route::get('/members/commodity-loan-repayment/{ippis}', 'CommodityController@commodityLoanRepayment')->name('members.commodityLoanRepayment');
Route::post('/members/commodity-loan-repayment/{ippis}', 'CommodityController@postCommodityLoanRepayment')->name('members.postCommodityLoanRepayment');
Route::get('/members/{loan_id}/commodity-term-loan-details', 'CommodityController@loanDetails')->name('members.commodityLoanDetails');

// long term loan
Route::get('/members/{long_term_loan_id}/long-term/payment-voucher/pdf', 'LongTermController@longTermLoansPaymentVoucherPDF')->name('members.longTermLoansPaymentVoucherPDF');
Route::get('/members/{long_term_loan_id}/long-term/payment-voucher', 'LongTermController@longTermLoansPaymentVoucher')->name('members.longTermLoansPaymentVoucher');
Route::get('/members/{ippis}/long-term', 'LongTermController@longTermLoans')->name('members.longTermLoans');
Route::get('/members/{ippis}/new-long-loan', 'LongTermController@newLongLoan')->name('members.newLongLoan');
Route::post('/members/post-new-long-loan/{ippis}', 'LongTermController@postNewLongLoan')->name('members.postNewLongLoan');
Route::get('/members/long-term-loan-repayment/{ippis}', 'LongTermController@longLoanRepayment')->name('members.longLoanRepayment');
Route::post('/members/long-term-loan-repayment/{ippis}', 'LongTermController@postLongLoanRepayment')->name('members.postLongLoanRepayment');
Route::get('/members/{loan_id}/long-term-loan-details', 'LongTermController@loanDetails')->name('members.longLoanDetails');

// ledger
Route::get('/import-initial-ledger-summary', 'LedgerController@getImportInitialLedgerSummary')->name('getImportInitialLedgerSummary');
Route::post('/import-initial-ledger-summary', 'LedgerController@postImportInitialLedgerSummary')->name('postImportInitialLedgerSummary');
Route::get('/import-initial-ledger', 'LedgerController@getImportInitialLedger')->name('getImportInitialLedger');
Route::post('/import-initial-ledger', 'LedgerController@postImportInitialLedger')->name('postImportInitialLedger');
Route::get('/ledger/{ippis}/entry', 'LedgerController@newledgerEntry')->name('newledgerEntry');
Route::post('/ledger/{ippis}/entry', 'LedgerController@postNewLedgerEntry')->name('postNewLedgerEntry');
Route::post('/ledger/entry/authorize', 'LedgerController@authorizeTransaction')->name('authorizeTransaction');
Route::put('/ledger/{ippis}/entry', 'LedgerController@updateLedgerEntry')->name('updateLedgerEntry');
Route::get('/ledger/check-account-balance/{bank_code}', 'LedgerController@checkAccountBalance');

// export ippis files
// Route::get('/ledger/generate-deductions', 'IppisDeductionsExportController@generateDeductions')->name('generateDeductions');
Route::get('/ledger/export-to-ippis', 'IppisDeductionsExportController@exportToIppis')->name('exportToIppis');
Route::post('/ledger/export-to-ippis/post', 'IppisDeductionsExportController@exportToIppisPost')->name('exportToIppisPost');
Route::get('/ledger/deductions/download/export/{id}/{month}/{year}/file', 'IppisDeductionsExportController@downloadIppisDeductionFile')->name('downloadIppisDeductionFile');


// import ippis files
Route::get('/ledger/import-from-ippis', 'IppisDeductionsImportController@importFromIppis')->name('importFromIppis');
Route::post('/ledger/import-from-ippis/post', 'IppisDeductionsImportController@importFromIppisPost')->name('importFromIppisPost');
Route::get('/ledger/deductions/download', 'IppisDeductionsImportController@downloadDeductions')->name('downloadDeductions');
Route::get('/ledger/deductions/download/{id}/{month}/{year}/file', 'IppisDeductionsImportController@downloadDeductionsFile')->name('downloadDeductionsFile');

// roles
Route::post('/role-permissions', 'RolesPermissionsController@rolePermissions')->name('rolePermissions');

// reports
Route::get('/reports/accounts', 'ReportsController@reportByAccounts')->name('reports.accounts');
Route::get('/reports', 'ReportsController@generalReportByPaypoint')->name('reports');
Route::get('/reports/pdf/{date_from}/{date_to}/{pay_point}', 'ReportsController@generalReportByPaypointPDF')->name('reportsPDF');
Route::get('/monthly-defaults', 'ReportsController@monthlyDefaults')->name('reports.monthlyDefaults');
Route::get('/loan-defaults', 'ReportsController@loanDefaults')->name('reports.loanDefaults');

// centers
Route::resource('/centers', 'CenterController');

// users
Route::get('/users/{id}/delete', 'UsersController@delete')->name('users.delete');
Route::get('/users/change-my-password', 'UsersController@changePassword')->name('users.changePassword');
Route::post('/users/change-password', 'UsersController@storeChangedPassword')->name('users.storeChangedPassword');
Route::resource('/users', 'UsersController');


// dashboard
Route::get('/dashboard', 'HomeController@index')->name('dashboard');

// awesomplete
Route::post('members/awesomplete', 'MembersController@awesomplete');

// settings
Route::get('settings/charges', 'SettingsController@charges')->name('settings.charges');

// bank charges
Route::post('settings/charges/bank/save', 'SettingsController@saveBankCharges')->name('settings.saveBankCharges');
Route::post('settings/charges/bank/edit', 'SettingsController@editBankCharges')->name('settings.editBankCharges');
Route::get('settings/charges/bank/delete/{id}', 'SettingsController@deleteBank')->name('settings.deleteBank');

// processing fee
Route::post('settings/charges/processing-fee/edit', 'SettingsController@editProcessingFee')->name('settings.editProcessingFee');

// withdrawal charge
Route::post('settings/charges/withdrawal-percentage-charge/edit', 'SettingsController@editWithdrawalPercentageCharge')->name('settings.editWithdrawalPercentageCharge');

// activity log
Route::get('activity/log/', 'ActivityLogController@getLog')->name('showActivityLog');
Route::get('activity/log/pdf/{date_from}/{date_to}', 'ActivityLogController@getLogPDF')->name('showActivityLogPDF');


// pending transactions
Route::get('pending-transactions', 'PendingTransactionsController@getPendingTransactions')->name('pendingTransactions');
Route::get('pending-transactions/{id}/{type}/start-process', 'PendingTransactionsController@startProcessing')->name('pendingTransactions.startProcessing');
Route::get('pending-transactions/{id}/{type}/process', 'PendingTransactionsController@processApplications')->name('pendingTransactions.processApplications');


// shares
Route::get('/shares/{ippis}/liquidate', 'SharesController@liquidate')->name('shareLiquidate');
Route::get('/shares/{ippis}/show', 'SharesController@sharesShow')->name('sharesShow');
Route::get('/shares/{ippis}/buy', 'SharesController@sharesBuy')->name('sharesBuy');
Route::post('/shares/buy/post', 'SharesController@sharesBuyPost')->name('sharesBuyPost');
Route::get('/shares/pay/{ippis}', 'SharesController@sharesPay')->name('sharesPay');
Route::post('/shares/authorize', 'SharesController@authorizeSharesTransaction')->name('authorizeSharesTransaction');
Route::get('/shares/bought', 'SharesController@sharesBought')->name('sharesBought');
Route::get('/shares/liquidated', 'SharesController@sharesLiquidated')->name('sharesLiquidated');

Route::get('/shares/settings', 'ShareSettingController@sharesSettings')->name('sharesSettings');
Route::post('/shares/settings/post', 'ShareSettingController@sharesSettingsPost')->name('sharesSettingsPost');


// loans duration settings
Route::get('/loans/settings', 'LoanSettingController@loanSettings')->name('loanSettings');
Route::post('/loans/settings/post', 'LoanSettingController@loanSettingsPost')->name('loanSettingsPost');




// accounting
Route::get('/coas', 'ChartOfAccountController@index')->name('coaIndex');
Route::get('/coas/newaccount', 'ChartOfAccountController@coaNewaccount')->name('coaNewaccount');
Route::post('/coas/postnewaccount', 'ChartOfAccountController@postCoaNewaccount')->name('coaNewaccountPost');

Route::get('accounting/new/account', 'Ledger_InternalController@newInternalLedgerAccount')->name('newInternalLedgerAccount');
Route::post('accounting/new/ledger', 'Ledger_InternalController@postNewInternalLedgerAccount')->name('postNewInternalLedgerAccount');
Route::get('accounting', 'Ledger_InternalController@balancesheet')->name('accountingIndex');


// accounting APIs
Route::get('api/accounting/new-expense', 'ExpenseController@newExpense');
Route::post('api/accounting/new-expense/post', 'ExpenseController@newExpensePost');
Route::get('api/accounting/make-ledger-entry', 'LedgerInternalController@makeLedgerEntry');
Route::post('api/accounting/make-ledger-entry/post', 'LedgerInternalController@makeLedgerEntryPost');
Route::get('api/accounting/journal', 'LedgerInternalController@journal');
Route::get('api/accounting/balance-sheet', 'LedgerInternalController@balancesheet');
Route::post('api/accounting/quick-balance', 'LedgerInternalController@quickBalance');
Route::get('api/accounting/{account_type}/balance/{date_from?}/{date_to?}', 'LedgerInternalController@accountTypeBalance');
Route::get('accounting/chart-of-accounts', 'LedgerInternalController@coa');
Route::get('api/accounting/chart-of-accounts', 'LedgerInternalController@coa');
Route::get('api/accounting/link-accounts', 'LedgerInternalController@linkAccounts');
Route::get('api/accounting/new-account/suggest-ledger-number/{parent_id}', 'LedgerInternalController@suggestLedgerNumber');
Route::post('api/accounting/link-accounts/post', 'LedgerInternalController@postLinkAccounts');
Route::get('api/accounting/new-account', 'LedgerInternalController@newAccount');
Route::post('api/accounting/new-account/post', 'LedgerInternalController@postNewAccount');
Route::get('api/accounting/accountype/chidlren/{accountType}', 'LedgerInternalController@accountTypeChildren');
Route::get('api/accounting/trial-balance', 'LedgerInternalController@trialBalance');
Route::get('api/accounting/{account_code}/account-ledger', 'LedgerInternalController@accountLedger');

// accounting
Route::get('accounting/single-leg-entry', 'LedgerInternalController@singleLegEntry')->name('singleLegEntry');
Route::post('accounting/single-leg-entry', 'LedgerInternalController@singleLegEntryPost')->name('singleLegEntryPost');
Route::get('accounting/', 'LedgerInternalController@index')->name('accountingIndex');
Route::get('accounting/balance-sheet', 'LedgerInternalController@balancesheet')->name('accountingBalanceSheet');
Route::get('accounting/profit-and-loss', 'LedgerInternalController@profitAndLoss')->name('accountingProfitAndLoss');
Route::get('accounting/journal', 'LedgerInternalController@journal')->name('accountingJournal');
Route::get('accounting/chart-of-accounts', 'LedgerInternalController@coa')->name('accountingCOA');
Route::get('accounting/link-accounts', 'LedgerInternalController@linkAccounts')->name('accountingLinkAccounts');
Route::get('accounting/quick-balance', 'LedgerInternalController@quickBalance')->name('accountingQuickBalance');
Route::get('accounting/make-ledger-entry', 'LedgerInternalController@makeLedgerEntry')->name('makeLedgerEntry');
Route::get('accounting/trial-balance', 'LedgerInternalController@trialBalance')->name('trialBalance');
Route::get('accounting/trial-balance/pdf/{dateFrom}/{dateTo}', 'LedgerInternalController@trialBalancePdf')->name('trialBalancePdf');
Route::get('accounting/{account_code}/account-ledger/pdf/{dateFrom}/{dateTo}', 'LedgerInternalController@accountLedgerPdf')->name('accountLedgerPdf');
Route::get('accounting/{account_code}/account-ledger', 'LedgerInternalController@accountLedger')->name('accountLedger');

// expenses accounting
Route::get('accounting/expenses', 'ExpenseController@index')->name('expensesIndex');
Route::get('accounting/expenses/{trxn_number}/pv', 'ExpenseController@pv')->name('expensesPv');
Route::get('accounting/expenses/{trxn_number}/pv-pdf', 'ExpenseController@expensesPvPdf')->name('expensesPvPdf');
Route::get('accounting/expenses/{trxn_number}/{status}/authorize', 'ExpenseController@authorizeTransaction')->name('authorize');
Route::get('accounting/expenses/new', 'ExpenseController@newExpense')->name('newExpense');
Route::post('accounting/expenses/new', 'ExpenseController@newExpensePost')->name('newExpensePost');

// suppliers
Route::get('suppliers/{id}/delete', 'SupplierController@delete')->name('suppliers.delete');
Route::resource('suppliers', 'SupplierController');

// Ledger snapshots
Route::post('/ledger/snap-shot/post', 'LedgerSnapShotController@ledgerSnapShotPost')->name('ledgerSnapShotPost');
Route::get('/ledger/snap-shot', 'LedgerSnapShotController@ledgerSnapShot')->name('ledgerSnapShot');
Route::get('/download-snapshot-file/{id}/{center}', 'LedgerSnapShotController@downloadSnapshotFile')->name('downloadSnapshotFile');


Route::get('tempLogs', 'TempActivityLogController@moveFromTempToActual');
Route::get('generateIPPIDDeductionFile', 'IppisDeductionsExportController@generateIPPIDDeductionFile');
Route::get('reconcileIppisImport', 'IppisDeductionsImportController@reconcileIppisImport');
Route::get('doInitialImport', 'InitialImportController@doInitialImport');
Route::get('accountstatement', 'AccountStatementController@generateMonthlyStatement');

// Ippis Trxns
Route::get('ippis/trxns', 'IppisTrxnsController@index')->name('ippis.trxns');
Route::get('ippis/debit-bank', 'IppisTrxnsController@debitBank')->name('ippis.debitBank');
Route::get('ippis/debit-bank/data', 'IppisTrxnsController@debitBankData')->name('ippis.debitBankData');
Route::post('ippis/debit-bank/post', 'IppisTrxnsController@debitBankPost')->name('ippis.debitBankPost');
Route::get('ippis/trxn/{trxnID}/details', 'IppisTrxnsController@trxnDetails')->name('ippis.trxnDetails');

// Inventory
Route::get('inventory/items', 'InventoryItemController@index')->name('inventory.index');
Route::get('inventory/items/create', 'InventoryItemController@create')->name('inventory.create');
Route::post('inventory/items/store', 'InventoryItemController@store')->name('inventory.store');
Route::get('inventory/items/{id}/store', 'InventoryItemController@edit')->name('inventory.edit');
Route::put('inventory/items/{id}/update', 'InventoryItemController@update')->name('inventory.update');
Route::get('inventory/items/{id}/delete', 'InventoryItemController@delete')->name('inventory.delete');


// ================================TEST ROUTES==============================
Route::get('listAccounts', 'AccountController@listAccounts');
Route::get('/test', function () {

    $ledger = new App\Ledger;
    $member = App\Member::where('ippis', 355925)->first();
    $expected = $ledger->getMemberExpectedMonthlyDeduction($member->ippis, '2020-08-30', 17679);
    $remitted = $ledger->getMemberRemittedMonthlyDeduction($member->ippis, '2020-08-30', 17679);
    $reconciled = $ledger->executeReconciliation($member->ippis, $remitted, $expected, 'trial', '2020-08-30', 17679);
    dd($member->ippis, $reconciled, 'in routes');

    
    $currentWithdrawalDate = Carbon::parse('2019-07-16');
    $lastWithdrawalDate = Carbon::parse('2020-07-16');
    dd($currentWithdrawalDate->diffInMonths($lastWithdrawalDate));
    

    dd($loan_date, $oBDeduction_for, $isDueToPenalizeLTL);

    // dd(microtime());
    $deduction_for = Carbon::parse('2020-07-30');
    $monthlyDeduction = [
        'ippis'                     => 482536,
        'full_name'                 => 'John',
        'monthly_savings_amount'    => 2000,
        'long_term_monthly_amount'  => 200000,
        'short_term_monthly_amount' => 0,
        'commodity_monthly_amount'  => 0,
        'total'                     => 200000,
        'deduction_for'             => $deduction_for,
        'month'                     => $deduction_for->format('m'),
        'year'                      => $deduction_for->format('Y'),
        'done_by'                   => 17679,
        'created_at'                => Carbon::now(),
        'updated_at'                => Carbon::now(),
    ];

    $ledger = new App\Ledger;
    $res = $ledger->executeDeductions('482536', 1000000, $monthlyDeduction, 'Test entry', $deduction_for, 17679);

    dd($res);

    
});