<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoanDurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_durations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');
            $table->string('duration');
            $table->string('number_of_months');
            $table->string('interest');
            $table->string('determinant_factor')->nullable()->comment('Number/Factor by which the amount allowable on a loan is determined eg Housing Loan = 3, LTL = 2');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_durations');
    }
}
