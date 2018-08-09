<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('username');
            $table->string('password');
            $table->string('phone_number');
            $table->string('card_number');
            $table->datetime('entry_date')->default('0000-00-00 00:00:00');
            $table->decimal('point', 8, 2)->default(0.0);
            $table->decimal('balance', 8, 2)->default(0.0);
            $table->datetime('next_period_date')->default('0000-00-00 00:00:00');
            $table->integer('periods')->default(0);
            $table->integer('recommends_reached')->default(0);
            $table->timestamps();
            
            $table->index([DB::raw('username')]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('members');
    }
}
