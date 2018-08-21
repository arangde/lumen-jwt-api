<?php

use Illuminate\Database\Seeder;

class PointSalesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create('zh_CN');
        
        factory(App\Item::class, 20)->create();

        $members = App\Member::all();
        $members->each(function ($member) use ($faker) {
            $count = $faker->numberBetween(0, 5);
            $member->pointSales()->saveMany(factory(App\PointSale::class, $count)->make());
        });
    }
}