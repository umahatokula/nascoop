<?php

use Illuminate\Database\Seeder;
use App\AccountType_Ex;

class AccountType_ExtTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\AccountType_Ex::truncate(); 

        AccountType_Ex::insert([
            'account_type_ext' => 'saving',
            'name' => 'Saving',
            'xact_type_code' => 'cr',
            'description' => 'Savings account type',
        ]);

        AccountType_Ex::insert([
            'account_type_ext' => 'ltl',
            'name' => 'Long Term Loans',
            'xact_type_code' => 'dr',
            'description' => 'Long Term Loans account type',
        ]);

        AccountType_Ex::insert([
            'account_type_ext' => 'stl',
            'name' => 'Short Term Loans',
            'xact_type_code' => 'dr',
            'description' => 'Short Term Loans account type',
        ]);

        AccountType_Ex::insert([
            'account_type_ext' => 'coml',
            'name' => 'COmmodity Loans',
            'xact_type_code' => 'dr',
            'description' => 'Commodity Loans account type',
        ]);

        AccountType_Ex::insert([
            'account_type_ext' => 'share',
            'name' => 'Share',
            'xact_type_code' => 'cr',
            'description' => 'Shares account type',
        ]);
    }
}
