<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLongTermPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('long_term_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('trxn_number')->nullable();
            $table->string('trxn_type')->nullable();
            $table->boolean('is_authorized')->default(0);
            $table->integer('ippis');
            $table->foreign('ippis')->references('ippis')->on('members')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('pay_point')->comment('members pay point');
            $table->integer('long_term_id');
            $table->string('ref')->nullable();
            $table->date('loan_date')->nullable();
            $table->date('deposit_date')->nullable();
            $table->double('dr', 15,2)->default(0.00);
            $table->double('cr', 15,2)->default(0.00);
            $table->double('bal', 15,2)->default(0.00);
            $table->string('month');
            $table->string('year');
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
        Schema::dropIfExists('long_term_payments');
    }
}
