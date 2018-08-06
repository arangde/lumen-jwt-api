<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnnouncementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->increments('id');
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('announcement_views', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('member_id')->unsigned();
            $table->integer('announcement_id')->unsigned();
            $table->datetime('read_date')->default('0000-00-00 00:00:00');

            $table->unique(array('member_id', 'announcement_id'));
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('announcement_views');
        Schema::dropIfExists('announcements');
    }
}
