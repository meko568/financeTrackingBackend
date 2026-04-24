<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\CacheService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();
        $categories = CacheService::getCategories($user->id);

        return $this->success(['categories' => $categories]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['required', 'string', 'max:191'],
            'type' => ['required', 'in:income,expense'],
            'color' => ['required', 'string', 'max:7'],
        ]);

        $category = Category::create([
            'name' => $request->name,
            'icon' => $request->icon,
            'type' => $request->type,
            'color' => $request->color,
            'user_id' => $request->user()->id,
            'is_default' => false,
        ]);

        $userId = $request->user()->id;
        CacheService::invalidateCategories($userId);

        return $this->success(['category' => $category], 'Category created', 201);
    }

    public function destroy(Category $category, Request $request)
    {
        if ($category->user_id !== $request->user()->id) {
            return $this->error('Category not found or cannot be deleted.', [], 404);
        }

        if ($category->transactions()->exists()) {
            return $this->error('Cannot delete category with associated transactions.', [], 422);
        }

        $category->delete();

        $userId = $request->user()->id;
        CacheService::invalidateCategories($userId);
        CacheService::invalidateTransactions($userId);

        return $this->success([], 'Category deleted');
    }
}
