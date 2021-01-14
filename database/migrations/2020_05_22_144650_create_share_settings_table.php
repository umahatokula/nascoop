<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShareSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('share_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->double('rate', 15, 2)->nullable()->comment('Conversion rate of money to units');
            $table->date('open_date')->nullable();
            $table->date('close_date')->nullable();
            $table->integer('done_by')->nullable()->comment('The member who set opeing and closing dates');
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
        Schema::dropIfExists('share_settings');
    }
}
