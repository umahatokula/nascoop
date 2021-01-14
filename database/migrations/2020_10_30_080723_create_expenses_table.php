<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('is_authorized')->dafault(0);
            $table->string('trxn_number')->nullable();
            $table->string('debit_account')->nullable();
            $table->string('credit_account')->nullable();
            $table->string('amount')->nullable();
            $table->string('description')->nullable();
            $table->integer('supplier_id')->nullable();
            $table->date('date')->nullable();
            $table->string('pv_number')->nullable();
            $table->integer('done_by')->nullable();
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
        Schema::dropIfExists('expenses');
    }
}
