<?php

namespace Database\Seeders;

use App\Models\AdminClass;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AdminClass::factory()->count(10)->create();
    }
}
