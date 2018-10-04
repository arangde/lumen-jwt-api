<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$faker = Faker\Factory::create('zh_CN');

$factory->define(App\User::class, function () {
    return [
        'name' => 'Super Manager',
        'email' => 'admin@test.com',
        'password' => app('hash')->make('12345'),
        'role' => 0
    ];
});

$factory->define(App\Member::class, function () use ($faker)  {
    return [
        'name' => $faker->name,
        'username' => $faker->userName,
        'password' => app('hash')->make('12345'),
        'phone_number' => $faker->phoneNumber,
        'card_number' => $faker->creditCardNumber,
        'entry_date' => $faker->dateTime(),
        'point' => $faker->randomFloat(2, 10, 100),
        'balance' => $faker->randomFloat(2, 10, 100),
        'next_period_date' => $faker->dateTimeBetween('now', '+7 days'),
    ];
});

$factory->define(App\Income::class, function () use ($faker)  {
    $type = $faker->numberBetween(0, 2);
    $old_amount = $faker->randomFloat(2, 10, 100);

    if ($type == 0) {
        $recurring_amount = $faker->randomFloat(2, 10, 100);
        $refers_amount = $faker->randomFloat(2, 10, 100);
        
        return [
            'old_amount' => $old_amount,
            'new_amount' => $old_amount + $recurring_amount,
            'recurring_amount' => $recurring_amount,
            'next_period_date' => $faker->dateTimeBetween('now', '+7 days'),
            'type' => 0,
            'note' => $faker->sentence(),
        ];
    } else if ($type == 1) {
        $refers_amount = $faker->randomFloat(2, 10, 100);
        
        return [
            'old_amount' => $old_amount,
            'new_amount' => $old_amount + $refers_amount,
            'refers_amount' => $refers_amount,
            'type' => 1,
            'note' => $faker->sentence(),
        ];
    } else {
        $direct_amount = $faker->randomFloat(2, 10, 100);

        return [
            'old_amount' => $old_amount,
            'new_amount' => $old_amount + $direct_amount,
            'direct_amount' => $direct_amount,
            'type' => 2,
            'note' => $faker->sentence(),
        ];
    }
});

$factory->define(App\Point::class, function () use ($faker)  {
    $old_point = $faker->randomFloat(2, 10, 100);

    return [
        'old_point' => $old_point,
        'new_point' => $old_point + $faker->randomFloat(2, 10, 100),
        'note' => $faker->sentence(),
    ];
});

$factory->define(App\Withdrawal::class, function () use ($faker)  {
    $status = $faker->numberBetween(0, 2);
    $created_at = $faker->dateTime();
    $days = $faker->numberBetween(2, 30);

    if ($status == 0) {
        return [
            'amount' => $faker->randomFloat(2, 10, 100),
            'created_at' => $created_at,
            'status' => 0,
            'note' => $faker->sentence(),
        ];    
    } elseif ($status == 1) {
        return [
            'amount' => $faker->randomFloat(2, 10, 100),
            'created_at' => $created_at,
            'accepted_date' => $faker->dateTimeBetween($created_at, '+'. $days. ' days'),
            'status' => 1,
            'note' => $faker->sentence(),
        ];
    } else {
        return [
            'amount' => $faker->randomFloat(2, 10, 100),
            'created_at' => $created_at,
            'rejected_date' => $faker->dateTimeBetween($created_at, '+'. $days. ' days'),
            'status' => 2,
            'note' => $faker->sentence(),
            'reject_reason' => $faker->text(),
        ];
    }
});

$factory->define(App\Announcement::class, function () use ($faker)  {
    return [
        'content' => $faker->sentence(10),
    ];
});

$factory->define(App\Item::class, function () use ($faker)  {
    return [
        'item_name' => $faker->sentence(2),
        'item_point' => $faker->randomFloat(2, 10, 100),
    ];
});

$factory->define(App\PointSale::class, function () use ($faker)  {
    $count = App\Item::count();
    $item_id = $faker->numberBetween(1, $count);
    $status = $faker->numberBetween(0, 2);
    $created_at = $faker->dateTime();
    $days = $faker->numberBetween(2, 30);

    if ($status == 0) {
        return [
            'item_id' => $item_id,
            'point' => $faker->randomFloat(2, 10, 100),
            'created_at' => $created_at,
            'status' => 0,
            'note' => $faker->sentence(),
        ];    
    } elseif ($status == 1) {
        return [
            'item_id' => $item_id,
            'point' => $faker->randomFloat(2, 10, 100),
            'created_at' => $created_at,
            'accepted_date' => $faker->dateTimeBetween($created_at, '+'. $days. ' days'),
            'status' => 1,
            'note' => $faker->sentence(),
        ];
    } else {
        return [
            'item_id' => $item_id,
            'point' => $faker->randomFloat(2, 10, 100),
            'created_at' => $created_at,
            'rejected_date' => $faker->dateTimeBetween($created_at, '+'. $days. ' days'),
            'status' => 2,
            'note' => $faker->sentence(),
            'reject_reason' => $faker->text(),
        ];
    }
});
