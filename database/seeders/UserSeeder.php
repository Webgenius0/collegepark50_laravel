<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Carbon;
use App\Models\BusinessProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'John',
     
                'email' => 'user@gmail.com',
                'role' => 'employee',
                'password' => Hash::make('12345678'),
               
            ],
            [
                'name' => 'Jane',
             
                'email' => 'admin@gmail.com',
                'role' => 'admin',
                'password' => Hash::make('12345678'),
                
            ],
            [
                'name' => 'Alice',
            
                'email' => 'business@gmail.com',
                'role' => 'company',
                'password' => Hash::make('12345678'),
               
            ],
            
        ]);
    
       
    }
    
}
