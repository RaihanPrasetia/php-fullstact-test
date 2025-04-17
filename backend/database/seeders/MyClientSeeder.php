<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MyClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('my_client')->insert([
            [
                'name' => 'Client One',
                'slug' => 'client-one',
                'is_project' => '1',
                'self_capture' => '1',
                'client_prefix' => 'CL1',
                'client_logo' => 'client-one-logo.jpg',
                'address' => '123 Main Street, City A',
                'phone_number' => '123456789',
                'city' => 'City A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Client Two',
                'slug' => 'client-two',
                'is_project' => '0',
                'self_capture' => '0',
                'client_prefix' => 'CL2',
                'client_logo' => 'client-two-logo.jpg',
                'address' => '456 Another Street, City B',
                'phone_number' => '987654321',
                'city' => 'City B',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add more client entries as needed
        ]);
    }
}
