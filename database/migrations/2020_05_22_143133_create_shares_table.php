<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSharesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shares', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('ippis')->nullable();
            $table->date('date_bought')->nullable();
            $table->integer('units')->nullable();
            $table->double('amount', 15, 2)->nullable();
            $table->double('rate_when_bought', 15, 2)->nullable()->comment('Conversion rate when shares were bought');
            $table->string('trxn_number')->nullable();
            $table->string('payment_method')->nullable();
            $table->boolean('is_authorized')->default(false)->nullable()->comment('P:Pending, A: Authorized, C:Cancelled');
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
        Schema::dropIfExists('shares');
    }
}
