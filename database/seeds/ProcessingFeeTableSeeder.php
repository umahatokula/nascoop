<?php

use Illuminate\Database\Seeder;
use App\ProcessingFee;

class ProcessingFeeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('processing_fees')->truncate();

        ProcessingFee::create([
            'amount' => 1000.00
        ]);
    }
}
