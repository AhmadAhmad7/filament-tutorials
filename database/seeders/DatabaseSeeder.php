<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Department;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        User::factory()->create([
            'name' =>'admin',
            'email'=>'admin@gmail.com',
            'password' =>Hash::make('1234'),
            'is_admin' =>true
        ]);
        $this->call(CountrySeeder::class);
       $this->call(StateSeeder::class);
       $this->call(CitySeeder::class);
    //    $this->call(UserSeeder::class);

        // Department::create(['name'=>'Laravel']);

    }
}
