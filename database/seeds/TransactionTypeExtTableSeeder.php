<?php

use Illuminate\Database\Seeder;
use App\TransactionType_Ext;

class TransactionTypeExtTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\TransactionType_Ext::truncate(); 

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'dp_S',
            'description' => 'Deposit to savings',
            'associated_trxns' => json_encode (json_decode ('{"cr": "210000", "dr": "121000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'wd_S',
            'description' => 'Withdrawal from savings',
            'associated_trxns' => json_encode (json_decode ('{"cr": "121000", "dr": "210000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'ltl',
            'description' => 'Long Term Loan',
            'associated_trxns' => json_encode (json_decode ('{"cr": "121000", "dr": "122000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'stl',
            'description' => 'Short Term Loan',
            'associated_trxns' => json_encode (json_decode ('{"cr": "121000", "dr": "124000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'coml',
            'description' => 'Commodity Loan',
            'associated_trxns' => json_encode (json_decode ('{"cr": "126002", "dr": "125000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'ltl_Rp_Deposit',
            'description' => 'Long Term Loan Repayment by Deposit',
            'associated_trxns' => json_encode (json_decode ('{"cr": "122000", "dr": "121000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'ltl_Rp_Savings',
            'description' => 'Long Term Loan Repayment from Savings',
            'associated_trxns' => json_encode (json_decode ('{"cr": "122000", "dr": "210000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'stl_Rp_Deposit',
            'description' => 'Short Term Loan Repayment by Deposit',
            'associated_trxns' => json_encode (json_decode ('{"cr": "124000", "dr": "121000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'stl_Rp_Savings',
            'description' => 'Short Term Loan Repayment from Savings',
            'associated_trxns' => json_encode (json_decode ('{"cr": "124000", "dr": "210000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'coml_Rp_Deposit',
            'description' => 'Commodity Loan Repayment by Deposit',
            'associated_trxns' => json_encode (json_decode ('{"cr": "125000", "dr": "121000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'coml_Rp_Savings',
            'description' => 'Commodity Loan Repayment from Savings',
            'associated_trxns' => json_encode (json_decode ('{"cr": "125000", "dr": "210000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'buy_shares',
            'description' => 'Buy shares',
            'associated_trxns' => json_encode (json_decode ('{"cr": "310000", "dr": "121000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'ls',
            'description' => 'Liquidate shares',
            'associated_trxns' => json_encode (json_decode ('{"cr": "", "dr": ""}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'ltl_interest',
            'description' => 'Interest on Long Term Loans',
            'associated_trxns' => json_encode (json_decode ('{"cr": "401000", "dr": "122000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'stl_interest',
            'description' => 'Interest on Short Term Loans',
            'associated_trxns' => json_encode (json_decode ('{"cr": "402000", "dr": "124000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'coml_interest',
            'description' => 'Interest on Commodity Loans',
            'associated_trxns' => json_encode (json_decode ('{"cr": "", "dr": ""}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'wd_interest',
            'description' => 'Interest on Withdrawals from Savings',
            'associated_trxns' => json_encode (json_decode ('{"cr": "404000", "dr": "210000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'wd_processing_fee',
            'description' => 'Withdrawal Processing Fees',
            'associated_trxns' => json_encode (json_decode ('{"cr": "407000", "dr": "210000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'ltl_processing_fee',
            'description' => 'LTL Processing Fees',
            'associated_trxns' => json_encode (json_decode ('{"cr": "407000", "dr": "122000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'stl_processing_fee',
            'description' => 'STL Processing Fees',
            'associated_trxns' => json_encode (json_decode ('{"cr": "407000", "dr": "124000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'coml_processing_fee',
            'description' => 'COML Processing Fees',
            'associated_trxns' => json_encode (json_decode ('{"cr": "407000", "dr": "125000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'savings_transfer_charges',
            'description' => 'Savings Bank Transfer Charges',
            'associated_trxns' => json_encode (json_decode ('{"cr": "507000", "dr": "210000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'ltl_transfer_charges',
            'description' => 'LTL Bank Transfer Charges',
            'associated_trxns' => json_encode (json_decode ('{"cr": "507000", "dr": "122000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'stl_transfer_charges',
            'description' => 'STL Bank Transfer Charges',
            'associated_trxns' => json_encode (json_decode ('{"cr": "507000", "dr": "124000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'coml_transfer_charges',
            'description' => 'COML Bank Transfer Charges',
            'associated_trxns' => json_encode (json_decode ('{"cr": "507000", "dr": "125000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'ltl_Adjustment',
            'description' => 'LTL Loan Adjustment',
            'associated_trxns' => json_encode (json_decode ('{"cr": "122000", "dr": "122000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'stl_Adjustment',
            'description' => 'STL Loan Adjustment',
            'associated_trxns' => json_encode (json_decode ('{"cr": "124000", "dr": "124000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'coml_Adjustment',
            'description' => 'Comm Loan Adjustment',
            'associated_trxns' => json_encode (json_decode ('{"cr": "125000", "dr": "125000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'ippis_non_remittance_savings',
            'description' => 'IPPIS Non-remittance savings',
            'associated_trxns' => json_encode (json_decode ('{"cr": "210000", "dr": ""}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'ippis_non_remittance_ltl',
            'description' => 'IPPIS Non-remittance ltl',
            'associated_trxns' => json_encode (json_decode ('{"cr": "", "dr": "122000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'ippis_non_remittance_stl',
            'description' => 'IPPIS Non-remittance stl',
            'associated_trxns' => json_encode (json_decode ('{"cr": "", "dr": "124000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'ippis_non_remittance_coml',
            'description' => 'IPPIS Non-remittance coml',
            'associated_trxns' => json_encode (json_decode ('{"cr": "", "dr": "125000"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'ippis_non_remittance_total',
            'description' => 'IPPIS Non-remittance coml',
            'associated_trxns' => json_encode (json_decode ('{"cr": "", "dr": "123003"}')),
        ]);

        TransactionType_Ext::insert([
            'xact_type_code_ext' => 'ippis_remittance',
            'description' => 'IPPIS Remittance',
            'associated_trxns' => json_encode (json_decode ('{"cr": "123003", "dr": ""}')),
        ]);

        // TransactionType_Ext::insert([
        //     'xact_type_code_ext' => 'ippis_remittance_savings',
        //     'description' => 'IPPIS Non-remittance savings',
        //     'associated_trxns' => json_encode (json_decode ('{"cr": "126000", "dr": ""}')),
        // ]);

        // TransactionType_Ext::insert([
        //     'xact_type_code_ext' => 'ippis_remittance_ltl',
        //     'description' => 'IPPIS Non-remittance ltl',
        //     'associated_trxns' => json_encode (json_decode ('{"cr": "", "dr": "126000"}')),
        // ]);

        // TransactionType_Ext::insert([
        //     'xact_type_code_ext' => 'ippis_remittance_stl',
        //     'description' => 'IPPIS Non-remittance stl',
        //     'associated_trxns' => json_encode (json_decode ('{"cr": "", "dr": "126000"}')),
        // ]);

        // TransactionType_Ext::insert([
        //     'xact_type_code_ext' => 'ippis_remittance_coml',
        //     'description' => 'IPPIS Non-remittance coml',
        //     'associated_trxns' => json_encode (json_decode ('{"cr": "", "dr": "126000"}')),
        // ]);
    }
}
