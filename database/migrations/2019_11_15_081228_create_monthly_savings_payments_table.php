<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMonthlySavingsPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monthly_savings_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('trxn_number')->nullable();
            $table->string('trxn_type')->nullable();
            $table->boolean('is_authorized')->default(0);
            $table->integer('ippis');
            $table->foreign('ippis')->references('ippis')->on('members')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('pay_point')->comment('members pay point');
            $table->integer('monthly_saving_id');
            $table->string('ref')->nullable();
            $table->date('withdrawal_date')->nullable();
            $table->date('deposit_date')->nullable()->comment('Date of deposit. This doubles as trxn date');
            $table->double('dr', 15,2)->default(0.00);
            $table->double('cr', 15,2)->default(0.00);
            $table->double('bal', 15,2)->default(0.00);
            $table->string('month');
            $table->string('year');
            $table->integer('is_withdrawal')->default(0)->comment('0 = deposit, 1 = withdrawal 2 = refund, 3 = shares bought');
            $table->double('processing_fee', 15,2)->nullable();
            $table->double('bank_charges', 15,2)->nullable();
            $table->double('interest', 15,2)->nullable();
            $table->string('interest_percentage')->nullable();
            $table->double('net_payment', 15,2)->nullable();
            $table->string('pv_number')->nullable();
            $table->integer('done_by')->nullable();
            $table->integer('applied_by')->nullable();
            $table->string('deposit_proof_path')->nullable();
            $table->string('deposit_proof_size')->nullable();
            $table->string('deposit_proof_extension')->nullable();
            $table->string('deposit_proof_original_name')->nullable();
            $table->string('deposit_proof_mime')->nullable();
            $table->boolean('start_processing')->default(0)->nullable();
            $table->boolean('is_approved')->default(0)->nullable();
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
        Schema::dropIfExists('monthly_savings_payments');
    }
}
