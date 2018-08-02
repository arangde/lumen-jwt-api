<?php

use Illuminate\Database\Seeder;

class AnnouncementViewsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker\Generator $faker)
    {
        factory(App\Announcement::class, 30)->create()->each(function ($announcement) use($faker) {
            $members = App\Member::all();
            $count = $members->count();
            $count1 = $faker->numberBetween(5, $count - 5);

            for ($i=0; $i<$count1; $i++) {
                $index1 = $faker->numberBetween(0, $count - 1);
                $member = $members->splice($index1, 1);
                $count--;

                if ($member->count()) {
                    App\AnnouncementView::create([
                        'member_id' => $member->get(0)->id,
                        'announcement_id' => $announcement->id,
                        'read_date' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        });
    }
}