<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('refers', function (Blueprint $table) {
            $table->dropForeign('refers_member_id_foreign');
            $table->dropForeign('refers_refer_id_foreign');
            $table->dropIndex('refers_refer_id_foreign');
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->dropForeign('incomes_member_id_foreign');
            $table->dropIndex('incomes_member_id_foreign');
        });

        Schema::table('points', function (Blueprint $table) {
            $table->dropForeign('points_member_id_foreign');
            $table->dropIndex('points_member_id_foreign');
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropForeign('withdrawals_member_id_foreign');
            $table->dropIndex('withdrawals_member_id_foreign');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign('sales_member_id_foreign');
            $table->dropIndex('sales_member_id_foreign');
        });

        Schema::table('redeems', function (Blueprint $table) {
            $table->dropForeign('redeems_member_id_foreign');
            $table->dropIndex('redeems_member_id_foreign');
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
