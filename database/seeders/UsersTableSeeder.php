<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('data/users.json')), true);

        // Admin
        $user = User::create([
            'name' => 'ROOT',
            'lastname' => 'ROOT',
            'second_lastname' => 'ROOT',
            'role' => 'admin',
            'email' => 'root@admin.com',
            'password' => bcrypt('root'),
        ]);

        foreach ($data as $key => $value) {
            Log::info('Seeding User: ' . $value['name']);
            try {
                User::create(array(
                    'name' => $value['name'],
                    'lastname' => $value['lastname'],
                    'second_lastname' => $value['second_lastname'],
                    'role' => $value['role'],
                    'email' => $value['email'],
                    'password' => $value['password']
                ));
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
