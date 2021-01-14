<?php

use Illuminate\Database\Seeder;
use App\Center;

class CentersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('centers')->truncate();

        Center::create([
            'name' => 'ARCSSTE',
            'transacting_bank_ledger_no' => '121001',
        ]);

        Center::create([
            'name' => 'ASTAL UYO',
            'transacting_bank_ledger_no' => '121001',
        ]);

        Center::create([
            'name' => 'CAR',
            'transacting_bank_ledger_no' => '121001',
        ]);

        Center::create([
            'name' => 'CBSS',
            'transacting_bank_ledger_no' => '121001',
        ]);

        Center::create([
            'name' => 'CGG',
            'transacting_bank_ledger_no' => '121001',
        ]);

        Center::create([
            'name' => 'COPINE',
            'transacting_bank_ledger_no' => '121001',
        ]);

        Center::create([
            'name' => 'CSTD',
            'transacting_bank_ledger_no' => '121004',
        ]);

        Center::create([
            'name' => 'CSTP',
            'transacting_bank_ledger_no' => '121001',
        ]);

        Center::create([
            'name' => 'NASRDA',
            'transacting_bank_ledger_no' => '121001',
        ]);

        Center::create([
            'name' => 'NCRS',
            'transacting_bank_ledger_no' => '121001',
        ]);

        Center::create([
            'name' => 'NIGCOMSAT',
            'transacting_bank_ledger_no' => '121001',
        ]);

        Center::create([
            'name' => 'ISSE',
            'transacting_bank_ledger_no' => '121001',
        ]);

        Center::create([
            'name' => 'ZASTAL KANO',
            'transacting_bank_ledger_no' => '121001',
        ]);

        Center::create([
            'name' => 'ZASTAL GOMBE',
            'transacting_bank_ledger_no' => '121001',
        ]);

        Center::create([
            'name' => 'ZASTAL IKWO EBONYI',
            'transacting_bank_ledger_no' => '121001',
        ]);

        Center::create([
            'name' => 'ZASTAL JOS',
            'transacting_bank_ledger_no' => '121001',
        ]);

        Center::create([
            'name' => 'AAEL OKA',
            'transacting_bank_ledger_no' => '121001',
        ]);

        Center::create([
            'name' => 'AAEL GUSAU',
            'transacting_bank_ledger_no' => '121001',
        ]);

        Center::create([
            'name' => 'AUAVL UBURU',
            'transacting_bank_ledger_no' => '121001',
        ]);

        Center::create([
            'name' => 'COOPERATIVE STAFF',
            'transacting_bank_ledger_no' => '121001',
        ]);
    }
}
