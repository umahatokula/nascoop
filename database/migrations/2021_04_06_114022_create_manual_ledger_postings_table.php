<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManualLedgerPostingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manual_ledger_postings', function (Blueprint $table) {
            $table->id();
            $table->string('ippis')->nullable();
            $table->string('trxn_number')->nullable();
            $table->string('debit_account')->nullable();
            $table->string('credit_account')->nullable();
            $table->double('amount', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('bank')->nullable();
            $table->date('value_date')->nullable();
            $table->boolean('is_authorized')->default(0)->nullable();
            $table->string('done_by')->nullable();
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
        Schema::dropIfExists('manual_ledger_postings');
    }
}
