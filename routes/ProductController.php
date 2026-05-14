<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Simulamos datos de una DB (En el futuro usarías Product::query())
        $allProducts = collect([
            ['id' => 1, 'name' => 'API DaVinci', 'category' => 'backend', 'price' => 50, 'img' => 'https://via.placeholder.com/200'],
            ['id' => 2, 'name' => 'UI Kit Aurora', 'category' => 'frontend', 'price' => 35, 'img' => 'https://via.placeholder.com/200'],
            ['id' => 3, 'name' => 'Scripts SEO Pro', 'category' => 'marketing', 'price' => 20, 'img' => 'https://via.placeholder.com/200'],
            ['id' => 4, 'name' => 'Plugin Auth', 'category' => 'backend', 'price' => 15, 'img' => 'https://via.placeholder.com/200'],
            ['id' => 5, 'name' => 'Dashboard React', 'category' => 'frontend', 'price' => 40, 'img' => 'https://via.placeholder.com/200'],
            ['id' => 6, 'name' => 'Bot Discord', 'category' => 'backend', 'price' => 10, 'img' => 'https://via.placeholder.com/200'],
        ]);

        // Filtros básicos
        $search = $request->query('search');
        $category = $request->query('category');

        $filtered = $allProducts->when($search, fn($c) => $c->filter(fn($p) => str_contains(strtolower($p['name']), strtolower($search))))
                                ->when($category, fn($c) => $c->where('category', $category));

        return response()->json($filtered->values());
    }
}