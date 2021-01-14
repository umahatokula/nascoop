<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLedgerInternalTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ledger_internal_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ledger_no')->nullable();
            $table->foreign('ledger_no')->references('ledger_no')->on('ledger__internals')->onUpdate('cascade')->onDelete('cascade');
            $table->string('date_time');
            $table->string('ledger_no_dr')->nullable();
            $table->foreign('ledger_no_dr')->references('ledger_no')->on('ledger__internals')->onUpdate('cascade')->onDelete('cascade');
            $table->double('amount', 15, 2);
            // $table->primary(['ledger_no', 'date_time']);
            $table->string('description')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ledger_internal_transactions');
    }
}
