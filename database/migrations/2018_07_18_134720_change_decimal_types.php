<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDecimalTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->decimal('points', 8, 2)->change();
            $table->decimal('balance', 8, 2)->change();
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->decimal('old_amount', 8, 2)->change();
            $table->decimal('new_amount', 8, 2)->change();
            $table->decimal('recurring_amount', 8, 2)->change();
            $table->decimal('refers_amount', 8, 2)->change();
            $table->decimal('direct_amount', 8, 2)->change();
        });

        Schema::table('points', function (Blueprint $table) {
            $table->decimal('old_point', 8, 2)->change();
            $table->decimal('new_point', 8, 2)->change();
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->decimal('amount', 8, 2)->change();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('product_price', 8, 2)->change();
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
