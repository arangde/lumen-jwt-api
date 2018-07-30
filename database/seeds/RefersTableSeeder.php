<?php

use Illuminate\Database\Seeder;

class RefersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker\Generator $faker)
    {
        $members = App\Member::all();
        $count = $members->count();
        $count1 = $faker->numberBetween(5, 10);

        for ($i=0; $i<$count1; $i++) {
            $index1 = $faker->numberBetween(0, $count - 1);
            $members1 = $members->splice($index1, 1);
            $count--;
            $count2 = $faker->numberBetween(0, 5);

            for ($j=0; $j<$count2; $j++) {
                if ($count > 0) {
                    $index2 = $faker->numberBetween(0, $count - 1);
                    $members2 = $members->splice($index2, 1);
                    $count--;

                    App\Refer::create([
                        'member_id' => $members2->get(0)->id,
                        'refer_id' => $members1->get(0)->id,
                        'refer_name' => $members1->get(0)->name,
                    ]);

                    $count3 = $faker->numberBetween(0, 5);

                    for ($k=0; $k<$count3; $k++) {
                        if ($count > 0) {
                            $index3 = $faker->numberBetween(0, $count - 1);
                            $members3 = $members->splice($index3, 1);
                            $count--;

                            App\Refer::create([
                                'member_id' => $members3->get(0)->id,
                                'refer_id' => $members2->get(0)->id,
                                'refer_name' => $members2->get(0)->name,
                            ]);
                        }
                    }
                }
            }
        }   
    }
}