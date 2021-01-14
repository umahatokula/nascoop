<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommodityLoanDefaultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commodity_loan_defaults', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('commodity_id');
            $table->integer('ippis');
            $table->foreign('ippis')->references('ippis')->on('members')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('pay_point')->comment('members pay point');
            $table->double('default_charge', 15,2);
            $table->double('monthly_obligation', 15,2)->comment('Amt obliged to pay monthly');
            $table->double('actual_paid', 15,2)->comment('Actual amount paid to service loan');
            $table->double('default_amount', 15,2)->comment('Amt remaining to be be paid per month -ie monthly_obligation - actual paid');
            $table->string('percentage')->comment('% used to calculate default_charge');
            $table->integer('month')->comment('month defaulted for');
            $table->integer('year')->comment('year defaulted for');
            $table->integer('applied_by')->nullable();
            $table->integer('done_by')->nullable();
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
        Schema::dropIfExists('commodity_loan_defaults');
    }
}
