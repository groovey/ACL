<?php

class Users extends Seeder
{
    public function init()
    {
        $faker = $this->faker;

        $this->define('users', function () use ($faker) {

            $status = ['active', 'inactive', 'deleted'];

            return [
                'role_id'    => rand(1, 5),
                'status'     => $status[array_rand($status)],
                'name'       => $faker->name,
                'email'      => $faker->email,
                'username'   => $faker->username,
                'password'   => $faker->password,
                'created_at' => $faker->dateTimeThisDecade($max = 'now'),
                'updated_at' => $faker->dateTimeThisYear($max = 'now'),
            ];
        }, $truncate = true);
    }

    public function run()
    {
        $this->seed(function ($counter) {
            $this->factory('users')->create();
        });

        return;
    }
}
