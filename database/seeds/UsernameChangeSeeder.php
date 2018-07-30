<?php

use Illuminate\Database\Seeder;

class UsernameChangeSeeder extends Seeder
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
            $member->username = explode('@', $member->email)[0];
            $member->save();
        });
    }
}