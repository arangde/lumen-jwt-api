<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointsalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pointsales', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('member_id')->unsigned();
            $table->integer('item_id')->unsigned();
            $table->decimal('point', 8, 2);
            $table->datetime('accepted_date')->default('0000-00-00 00:00:00');
            $table->datetime('rejected_date')->default('0000-00-00 00:00:00');
            $table->tinyInteger('status');
            $table->text('note');
            $table->text('reject_reason');
            $table->timestamps();

            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pointsales');
    }
}
