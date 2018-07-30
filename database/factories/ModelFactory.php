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

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => app('hash')->make('12345'),
        'role' => $faker->numberBetween(0, 2),
    ];
});

$factory->define(App\Member::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => app('hash')->make('12345'),
        'phone_number' => $faker->tollFreePhoneNumber,
        'card_number' => $faker->creditCardNumber,
        'entry_date' => $faker->dateTime(),
        'point' => $faker->randomFloat(2, 10, 100),
        'balance' => $faker->randomFloat(2, 10, 100),
        'next_period_date' => $faker->dateTimeBetween('now', '+7 days'),
    ];
});

$factory->define(App\Income::class, function (Faker\Generator $faker) {
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

$factory->define(App\Point::class, function (Faker\Generator $faker) {
    $old_point = $faker->randomFloat(2, 10, 100);

    return [
        'old_point' => $old_point,
        'new_point' => $old_point + $faker->randomFloat(2, 10, 100),
        'note' => $faker->sentence(),
    ];
});

$factory->define(App\Withdrawal::class, function (Faker\Generator $faker) {
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

$factory->define(App\Sale::class, function (Faker\Generator $faker) {
    return [
        'product_name' => $faker->sentence(2),
        'product_price' => $faker->randomFloat(2, 10, 100),
    ];
});

$factory->define(App\Redeem::class, function (Faker\Generator $faker) {
    $status = $faker->numberBetween(0, 2);
    $created_at = $faker->dateTime();
    $days = $faker->numberBetween(2, 30);

    if ($status == 0) {
        return [
            'point' => $faker->randomFloat(2, 10, 100),
            'created_at' => $created_at,
            'status' => 0,
            'note' => $faker->sentence(),
        ];    
    } elseif ($status == 1) {
        return [
            'point' => $faker->randomFloat(2, 10, 100),
            'created_at' => $created_at,
            'accepted_date' => $faker->dateTimeBetween($created_at, '+'. $days. ' days'),
            'status' => 1,
            'note' => $faker->sentence(),
        ];
    } else {
        return [
            'point' => $faker->randomFloat(2, 10, 100),
            'created_at' => $created_at,
            'rejected_date' => $faker->dateTimeBetween($created_at, '+'. $days. ' days'),
            'status' => 2,
            'note' => $faker->sentence(),
            'reject_reason' => $faker->text(),
        ];
    }
});
