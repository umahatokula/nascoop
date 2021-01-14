<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLedgerInternalStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ledger_internal_statements', function (Blueprint $table) {
            $table->string('ledger_no');
            $table->date('date', 0);
            $table->double('closing_balance', 15, 2);
            $table->primary(['ledger_no', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ledger_internal_statements');
    }
}
