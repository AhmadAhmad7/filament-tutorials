<?php

namespace Database\Seeders;

use DB;
use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->created([
            'name' => 'admin',
            'email' =>'admin@gmail.com',
            'password' =>Hash::make('1234')
        ]);
    }
}
