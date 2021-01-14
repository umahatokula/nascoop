require('./bootstrap');

window.Vue = require('vue');
Vue.config.devtools = true

import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css';
Vue.component('v-select', vSelect)

import VueGoodTablePlugin from 'vue-good-table';
// import the styles 
import 'vue-good-table/dist/vue-good-table.css'
Vue.use(VueGoodTablePlugin);

// moment js
Vue.use(require('vue-moment'));


Vue.filter('number_format', function (number, decimals = 2, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
});


// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

Vue.component('newexpense', require('./components/Accounting/NewExpense.vue').default);
Vue.component('debit-bank', require('./components/IppisTrxns/DebitBank.vue').default);
Vue.component('accountledger', require('./components/Accounting/AccountLedger.vue').default);
Vue.component('trialbalance', require('./components/Accounting/TrialBalance.vue').default);
Vue.component('makeledgerentry', require('./components/Accounting/MakeLedgerEntry.vue').default);
Vue.component('quickbalance', require('./components/Accounting/QuickBalance.vue').default);
Vue.component('journalentries', require('./components/Accounting/JournalEntries.vue').default);
Vue.component('Expenses', require('./components/Accounting/Reports/Expenses.vue').default);
Vue.component('Income', require('./components/Accounting/Reports/Income.vue').default);
Vue.component('Equity', require('./components/Accounting/Reports/Equity.vue').default);
Vue.component('Liabilities', require('./components/Accounting/Reports/Liabilities.vue').default);
Vue.component('Assets', require('./components/Accounting/Reports/Assets.vue').default);
Vue.component('profitandloss', require('./components/Accounting/Reports/ProfitAndLoss.vue').default);
Vue.component('balancesheet', require('./components/Accounting/Reports/BalanceSheet.vue').default);
Vue.component('new-ledger-account', require('./components/LedgerInternalAccounts/NewAccount.vue').default);
Vue.component('NewAccount', require('./components/Accounting/NewAccount.vue').default);
Vue.component('link-accounts', require('./components/Accounting/LinkAccounts.vue').default);
Vue.component('coa', require('./components/Accounting/COA.vue').default);
Vue.component('accounting', require('./components/Accounting/Index.vue').default);
Vue.component('comm-loans-durations', require('./components/LoansDurations/CommLoansDurations.vue').default);
Vue.component('stl-loans-durations', require('./components/LoansDurations/StlLoansDurations.vue').default);
Vue.component('ltl-loans-durations', require('./components/LoansDurations/LtlLoansDurations.vue').default);
Vue.component('shares-buy', require('./components/Shares/Buy.vue').default);
Vue.component('new-ledger-entry', require('./components/Ledger/NewLedgerEntry.vue').default);
Vue.component('edit-ledger-entry', require('./components/Ledger/EditLedgerEntry.vue').default);
Vue.component('new-long-term-loan', require('./components/LongTermLoans/NewLongTermLoan.vue').default);
Vue.component('long-term-loan-repayment', require('./components/LongTermLoans/LongTermLoanRepayment.vue').default);
Vue.component('new-short-term-loan', require('./components/ShortTermLoans/NewShortTermLoan.vue').default);
Vue.component('short-term-loan-repayment', require('./components/ShortTermLoans/ShortTermLoanRepayment.vue').default);
Vue.component('new-commodity-loan', require('./components/Commodity/NewCommodityLoan.vue').default);
Vue.component('commodity-loan-repayment', require('./components/Commodity/CommodityLoanRepayment.vue').default);
Vue.component('monthly-savings-withdrawal', require('./components/MonthlySavings/Withdrawal.vue').default);
Vue.component('add-user', require('./components/Users/AddUser.vue').default);
Vue.component('banks', require('./components/Settings/Banks.vue').default);
Vue.component('processingfee', require('./components/Settings/ProcessingFee.vue').default);
Vue.component('withdrawalsettings', require('./components/Settings/Withdrawal.vue').default);

const app = new Vue({
    el: '#app',
});
