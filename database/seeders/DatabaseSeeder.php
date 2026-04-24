<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Salary', 'icon' => '💼', 'type' => 'income', 'color' => '#10B981', 'is_default' => true],
            ['name' => 'Freelance', 'icon' => '💻', 'type' => 'income', 'color' => '#3B82F6', 'is_default' => true],
            ['name' => 'Investment', 'icon' => '📈', 'type' => 'income', 'color' => '#8B5CF6', 'is_default' => true],
            ['name' => 'Other Income', 'icon' => '💰', 'type' => 'income', 'color' => '#F59E0B', 'is_default' => true],
            ['name' => 'Food', 'icon' => '🍔', 'type' => 'expense', 'color' => '#F43F5E', 'is_default' => true],
            ['name' => 'Transport', 'icon' => '🚗', 'type' => 'expense', 'color' => '#F97316', 'is_default' => true],
            ['name' => 'Housing', 'icon' => '🏠', 'type' => 'expense', 'color' => '#EF4444', 'is_default' => true],
            ['name' => 'Healthcare', 'icon' => '🏥', 'type' => 'expense', 'color' => '#06B6D4', 'is_default' => true],
            ['name' => 'Shopping', 'icon' => '🛍️', 'type' => 'expense', 'color' => '#EC4899', 'is_default' => true],
            ['name' => 'Entertainment', 'icon' => '🎮', 'type' => 'expense', 'color' => '#A855F7', 'is_default' => true],
            ['name' => 'Education', 'icon' => '📚', 'type' => 'expense', 'color' => '#14B8A6', 'is_default' => true],
            ['name' => 'Other Expense', 'icon' => '📦', 'type' => 'expense', 'color' => '#6B7280', 'is_default' => true],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate([
                'name' => $category['name'],
                'type' => $category['type'],
            ], $category);
        }
    }
}
