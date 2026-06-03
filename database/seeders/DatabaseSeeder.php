<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(DepartmentSeeder::class);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'department_id' => 1,
        ]);

        User::factory()->create([
            'name' => 'María López',
            'email' => 'maria@example.com',
            'department_id' => 1,
        ]);

        User::factory()->create([
            'name' => 'Carlos Ventas',
            'email' => 'carlos@example.com',
            'department_id' => 2,
        ]);
    }
}
