<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\AuthController;

// API Routes

// Auth
Route::post('/login',   [AuthController::class, 'login']);
Route::post('/logout',  [AuthController::class, 'logout']);
Route::get('/me',       [AuthController::class, 'me']);

// Products
Route::get('/products',          [ProductController::class, 'index']);
Route::get('/products/{id}',     [ProductController::class, 'show']);
Route::post('/products',         [ProductController::class, 'store']);
Route::put('/products/{id}',     [ProductController::class, 'update']);
Route::delete('/products/{id}',  [ProductController::class, 'destroy']);

// Cart
Route::get('/cart',              [CartController::class, 'index']);
Route::post('/cart',             [CartController::class, 'store']);
Route::put('/cart/{id}',         [CartController::class, 'update']);
Route::delete('/cart/{id}',      [CartController::class, 'destroy']);
