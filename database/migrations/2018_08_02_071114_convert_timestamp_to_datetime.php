<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertTimestampToDatetime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->datetime('rejected_date')->change();
            // $table->string('rejected_date')->change();
        });

        Schema::table('redeems', function (Blueprint $table) {
            $table->datetime('rejected_date')->change();
            // $table->string('rejected_date')->change();
        });

        Schema::table('members', function (Blueprint $table) {
            $table->datetime('next_period_date')->change();
            // $table->string('next_period_date')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
