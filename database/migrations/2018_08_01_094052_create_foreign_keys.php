<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('refers', function (Blueprint $table) {
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
            $table->foreign('refer_id')->references('id')->on('members')->onDelete('cascade');
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
        });

        Schema::table('points', function (Blueprint $table) {
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
        });

        Schema::table('redeems', function (Blueprint $table) {
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
        });

        Schema::table('redeems', function (Blueprint $table) {
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
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
