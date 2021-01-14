<?php

use Illuminate\Database\Seeder;
use App\AccountType;

class AccountTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\AccountType::truncate(); 

        AccountType::insert([
            'account_type' => 'asset',
            'name' => 'Assets',
            'description' => 'Assets of NASCOOP',
        ]);

        AccountType::insert([
            'account_type' => 'liability',
            'name' => 'Liabilities',
            'description' => 'Liabilities of NASCOOP',
        ]);

        AccountType::insert([
            'account_type' => 'equity',
            'name' => 'Equity',
            'description' => 'Equity of NASCOOP',
        ]);

        AccountType::insert([
            'account_type' => 'income',
            'name' => 'Income',
            'description' => 'Income of NASCOOP',
        ]);

        AccountType::insert([
            'account_type' => 'expense',
            'name' => 'Expenses',
            'description' => 'Expenses of NASCOOP',
        ]);
    }
}
