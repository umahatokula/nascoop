<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLedgerInternalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ledger__internals', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('ledger_no')->unique();
            $table->string('account_type');
            $table->string('account_name');

            $table->string('usage')->nullable();
            $table->boolean('allow_manual_journal_entries')->nullable()->default(true);
            $table->boolean('ignore_trailing_zeros')->nullable()->default(true);
            $table->boolean('use_centers_as_detail_accounts')->nullable()->default(false);
            $table->string('prefix_text')->nullable();
            $table->boolean('show_in_report_as_header')->nullable()->default(false);
            $table->boolean('show_total_amount_in_report')->nullable()->default(true);
            $table->text('description')->nullable();

            $table->integer('level')->nullable();
            $table->integer('parent_id')->nullable();
            $table->boolean('status')->nullable()->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ledger__internals');
    }
}
