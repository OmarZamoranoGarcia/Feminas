<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\TestProductController;

Route::get('/', [WelcomeController::class, 'index'])->name('home');

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/admin', function () {
    return view('admin');
})->name('admin');

Route::get('/register', function () {
    return view('register');
})->name('register');

Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::get('/test-product', [TestProductController::class, 'createTestProduct']);

Route::get('/check-db', function () {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'success' => true,
            'database' => DB::connection()->getDatabaseName(),
            'message' => 'Conexión establecida correctamente con los datos del .env.'
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});
