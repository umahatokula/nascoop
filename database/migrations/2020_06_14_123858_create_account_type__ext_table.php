<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountTypeExtTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_type__ext', function (Blueprint $table) {
            $table->string('account_type_ext');
            $table->string('name');
            $table->string('xact_type_code');
            $table->text('description')->nullable();
            $table->double('fee', 15, 2)->nullable();
            $table->primary('account_type_ext');
            $table->unique('account_type_ext');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_type__ext');
    }
}
