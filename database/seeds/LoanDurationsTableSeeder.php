<?php

use Illuminate\Database\Seeder;
use App\LoanDuration;

class LoanDurationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('loan_durations')->truncate();

        // LTL
        $loanDuration                     = new LoanDuration;
        $loanDuration->code               = 'ltl';
        $loanDuration->duration           = '72 Months';
        $loanDuration->number_of_months   = 72;
        $loanDuration->interest           = 6;
        $loanDuration->determinant_factor = 4;
        $loanDuration->save();

        $loanDuration                     = new LoanDuration;
        $loanDuration->code               = 'ltl';
        $loanDuration->duration           = '36 Months';
        $loanDuration->number_of_months   = 36;
        $loanDuration->interest           = 15;
        $loanDuration->determinant_factor = 3;
        $loanDuration->save();

        $loanDuration                     = new LoanDuration;
        $loanDuration->code               = 'ltl';
        $loanDuration->duration           = '20 Months';
        $loanDuration->number_of_months   = 20;
        $loanDuration->interest           = 10;
        $loanDuration->determinant_factor = 2;
        $loanDuration->save();

        $loanDuration                     = new LoanDuration;
        $loanDuration->code               = 'ltl';
        $loanDuration->duration           = '15 Months';
        $loanDuration->number_of_months   = 15;
        $loanDuration->interest           = 7.5;
        $loanDuration->determinant_factor = 2;
        $loanDuration->save();

        $loanDuration                     = new LoanDuration;
        $loanDuration->code               = 'ltl';
        $loanDuration->duration           = '10 Months';
        $loanDuration->number_of_months   = 10;
        $loanDuration->interest           = 5;
        $loanDuration->determinant_factor = 2;
        $loanDuration->save();

        // STL
        $loanDuration                     = new LoanDuration;
        $loanDuration->code               = 'stl';
        $loanDuration->duration           = '5 Months';
        $loanDuration->number_of_months   = 5;
        $loanDuration->interest           = 5;
        $loanDuration->determinant_factor = 500000;
        $loanDuration->save();

        $loanDuration                     = new LoanDuration;
        $loanDuration->code               = 'stl';
        $loanDuration->duration           = '4 Months';
        $loanDuration->number_of_months   = 4;
        $loanDuration->interest           = 4;
        $loanDuration->determinant_factor = 500000;
        $loanDuration->save();

        $loanDuration                     = new LoanDuration;
        $loanDuration->code               = 'stl';
        $loanDuration->duration           = '3 Months';
        $loanDuration->number_of_months   = 3;
        $loanDuration->interest           = 3;
        $loanDuration->determinant_factor = 500000;
        $loanDuration->save();

        // COMM
        $loanDuration                   = new LoanDuration;
        $loanDuration->code             = 'comm';
        $loanDuration->duration         = '15 Months';
        $loanDuration->number_of_months = 15;
        $loanDuration->interest         = 5;
        $loanDuration->save();

        $loanDuration                   = new LoanDuration;
        $loanDuration->code             = 'comm';
        $loanDuration->duration         = '10 Months';
        $loanDuration->number_of_months = 10;
        $loanDuration->interest         = 7;
        $loanDuration->save();

        $loanDuration                   = new LoanDuration;
        $loanDuration->code             = 'comm';
        $loanDuration->duration         = '5 Months';
        $loanDuration->number_of_months = 5;
        $loanDuration->interest         = 3;
        $loanDuration->save();
    }
}
