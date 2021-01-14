<?php

use Illuminate\Database\Seeder;

class BanksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\Bank::truncate();   

        \DB::statement("INSERT INTO `banks` (`name`,`code`, `transfer_charge`) VALUES
        ('Access Bank','044', 0.00),
        ('Citibank','023', 10.50),
        ('Access Bank (Diamond Bank)','063', 10.50),
        ('Dynamic Standard Bank','000 ', 10.50),
        ('Ecobank Nigeria','050', 10.50),
        ('Fidelity Bank Nigeria','070', 10.50),
        ('First Bank of Nigeria','011', 10.50),
        ('First City Monument Bank','214', 10.50),
        ('Guaranty Trust Bank','058', 10.50),
        ('Heritage Bank Plc','030', 10.50),
        ('Jaiz Bank','301', 10.50),
        ('Keystone Bank Limited','082', 10.50),
        ('Providus Bank Plc','101', 10.50),
        ('Polaris Bank','076', 10.50),
        ('Stanbic IBTC Bank Nigeria Limited','221', 10.50),
        ('Standard Chartered Bank','068', 10.50),
        ('Sterling Bank','232', 10.50),
        ('Suntrust Bank Nigeria Limited','100', 10.50),
        ('Union Bank of Nigeria','032', 10.50),
        ('United Bank for Africa','033', 10.50),
        ('Unity Bank Plc','215', 10.50),
        ('Wema Bank','035', 10.50),
        ('Zenith Bank','057', 10.50);");
    }
}
