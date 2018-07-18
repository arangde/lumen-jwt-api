<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIncomesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('member_id')->unsigned();
            $table->decimal('old_amount', 5, 2);
            $table->decimal('new_amount', 5, 2);
            $table->decimal('recurring_amount', 5, 2);
            $table->decimal('refers_amount', 5, 2);
            $table->decimal('direct_amount', 5, 2);
            $table->timestamp('next_period_date');
            $table->tinyInteger('type');
            $table->text('note');
            $table->timestamps();

            $table->foreign('member_id')->references('id')->on('members');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('incomes');
    }
}
