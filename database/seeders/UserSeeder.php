<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([

            [
                'f_name' => 'Sarah',
                'l_name' => 'Wilson',
                'email' => 'admin@gmail.com',
                'role' => 'admin',
                'password' => Hash::make('12345678'),
                'is_otp_verified' => true,
            ],
            [
                'f_name' => 'John',
                'l_name' => 'Doe',
                'email' => 'user@gmail.com',
                'role' => 'user',
                'password' => Hash::make('12345678'),
                'is_otp_verified' => true,
            ],
            [
                'f_name' => 'Jane',
                'l_name' => 'Smith',
                'email' => 'dj@gmail.com',
                'role' => 'dj',
                'password' => Hash::make('12345678'),
                'is_otp_verified' => true,
            ],
            [
                'f_name' => 'Mike',
                'l_name' => 'Johnson',
                'email' => 'promoter@gmail.com',
                'role' => 'promoter',
                'password' => Hash::make('12345678'),
                'is_otp_verified' => true,
            ],
            [
                'f_name' => 'Emily',
                'l_name' => 'Brown',
                'email' => 'artist@gmail.com',
                'role' => 'artist',
                'password' => Hash::make('12345678'),
                'is_otp_verified' => true,
            ],
            [
                'f_name' => 'Robert',
                'l_name' => 'Taylor',
                'email' => 'venue@gmail.com',
                'role' => 'venue',
                'password' => Hash::make('12345678'),
                'is_otp_verified' => true,
            ],

        ]);
    }
}
