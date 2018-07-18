<?php

use Illuminate\Database\Seeder;

class MembersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Member::class, 50)->create()->each(function ($member) {
            $member->incomes()->saveMany(factory(App\Income::class, 10)->make());
            $member->pointsList()->saveMany(factory(App\Point::class, 10)->make());
            $member->withdrawals()->saveMany(factory(App\Withdrawal::class, 10)->make());
            $member->sales()->saveMany(factory(App\Sale::class, 10)->make());
        });
    }
}