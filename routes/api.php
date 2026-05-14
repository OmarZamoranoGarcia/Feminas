<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;

/*
| API Routes
*/
Route::get('/products', [ProductController::class, 'index']);