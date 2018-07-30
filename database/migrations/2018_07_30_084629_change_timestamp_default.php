<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTimestampDefault extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->datetime('accepted_date')->default('0000-00-00 00:00:00')->change();
            $table->datetime('rejected_date')->default('0000-00-00 00:00:00')->change();
        });

        Schema::table('redeems', function (Blueprint $table) {
            $table->datetime('accepted_date')->default('0000-00-00 00:00:00')->change();
            $table->datetime('rejected_date')->default('0000-00-00 00:00:00')->change();
        });

        Schema::table('members', function (Blueprint $table) {
            $table->datetime('entry_date')->default('0000-00-00 00:00:00')->change();
            $table->datetime('next_period_date')->default('0000-00-00 00:00:00')->change();
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->datetime('next_period_date')->default('0000-00-00 00:00:00')->change();
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
