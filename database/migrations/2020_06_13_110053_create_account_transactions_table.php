<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->string('ledger_no');
            $table->string('date_time');
            $table->string('xact_type_code');
            $table->string('xact_type_code_ext');
            $table->integer('account_no')->nullable();
            $table->foreign('account_no')->references('account_no')->on('accounts')->onUpdate('cascade')->onDelete('cascade');
            $table->string('account_type_ext')->nullable();
            $table->double('amount', 15, 2);
            $table->primary(['ledger_no', 'date_time']);
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
        Schema::dropIfExists('account_transactions');
    }
}
