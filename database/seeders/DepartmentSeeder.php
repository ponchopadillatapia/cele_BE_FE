<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Tecnología', 'slug' => 'tecnologia'],
            ['name' => 'Ventas', 'slug' => 'ventas'],
            ['name' => 'Recursos Humanos', 'slug' => 'recursos-humanos'],
            ['name' => 'Marketing', 'slug' => 'marketing'],
            ['name' => 'Finanzas', 'slug' => 'finanzas'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['slug' => $dept['slug']], $dept);
        }
    }
}
