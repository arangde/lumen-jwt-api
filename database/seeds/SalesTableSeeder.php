<?php

use Illuminate\Database\Seeder;

class SalesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Sale::truncate();
        $setting_product_price = \App\Setting::where('setting_field', 'product_price')->first();
        if ($setting_product_price) {
            $product_price = intval($setting_product_price->value);
            $members = \App\Member::all();
            $members->each(function ($member) use($product_price) {
                $sale = new \App\Sale;
                $sale->product_price = $product_price;
                $sale->created_at = $member->created_at;
                $member->sales()->save($sale);
            });
        }
    }
}