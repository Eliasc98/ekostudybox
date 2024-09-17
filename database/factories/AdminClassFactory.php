<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdminClass>
 */
class AdminClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $classNames = [
            'Class 7',
            'Class 8',
            'Class 9',
            'Class 10',
            'Class 11',
            'Class 12',
            'Class 13',
            'Class 14',
            'Class 15',
            'Class 16',
        ];
    
        $createdAt = now();
        $updatedAt = $createdAt;
    
        return [
            'class_name' => $classNames,
            'total_num_of_students' => $this->faker->randomNumber(),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }
}
