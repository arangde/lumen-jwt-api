<?php

use Illuminate\Database\Seeder;

class MemberEntryDateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker\Generator $faker)
    {
        $members = App\Member::all();
        $members->each(function ($member) use($faker) {
            $member->entry_date = $faker->dateTimeBetween('-100 days', '-5 days');
            $member->save();
        });
    }
}