<?php

use Illuminate\Database\Seeder;
use App\WithdrawalSetting;

class WithdrawalSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('withdrawal_settings')->truncate();

        WithdrawalSetting::create([
            'name' => 'Withdrawal Percentage Charge',
            'type' => 'withdrawal_percentage_charge',
            'value' => 2.5
        ]);
    }
}
