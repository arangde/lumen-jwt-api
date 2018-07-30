<?php

use Illuminate\Database\Seeder;

class RedeemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $members = App\Member::all();
        $members->each(function ($member) {
            $member->redeems()->saveMany(factory(App\Redeem::class, 8)->make());
        });
    }
}