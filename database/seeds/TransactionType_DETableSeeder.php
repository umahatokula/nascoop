<?php

use Illuminate\Database\Seeder;
use App\TransactionTypeDoubleEntry;

class TransactionType_DETableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\TransactionTypeDoubleEntry::truncate(); 

        TransactionTypeDoubleEntry::insert([
            'xact_type_code' => 'cr',
            'name' => 'Credit',
        ]);

        TransactionTypeDoubleEntry::insert([
            'xact_type_code' => 'dr',
            'name' => 'Dedit',
        ]);
    }
}
