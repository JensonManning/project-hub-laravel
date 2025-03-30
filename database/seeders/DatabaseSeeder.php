<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run the role seeder first
        $this->call(RoleSeeder::class);
        
        // Get the User role ID
        $userRoleId = Role::where('name', 'User')->first()->id;
        $adminRoleId = Role::where('name', 'Admin')->first()->id;

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'role_id' => $userRoleId,
        ]);

        User::factory()->create([
            'name' => 'Jenson',
            'email' => 'jensonmanningpro@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRoleId,
        ]);
    }
}
