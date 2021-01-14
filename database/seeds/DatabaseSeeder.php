<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RolesAndPermissionsTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(CentersTableSeeder::class);
        $this->call(BanksTableSeeder::class);
        $this->call(ProcessingFeeTableSeeder::class);
        $this->call(ShareSettingsTableSeeder::class);
        $this->call(ChartOfAccountsTableSeeder::class);
        $this->call(AccountTypesTableSeeder::class);
        $this->call(TransactionType_DETableSeeder::class);
        $this->call(AccountType_ExtTableSeeder::class);
        $this->call(EntityTypeTableSeeder::class);
        $this->call(LoanDurationsTableSeeder::class);
        $this->call(TransactionTypeExtTableSeeder::class);
        $this->call(WithdrawalSettingSeeder::class);
    }
}
