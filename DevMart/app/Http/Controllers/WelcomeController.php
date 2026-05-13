<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        $userCount = 100; // Aquí podrías luego consultar tu base de datos

        return view('welcome', compact('userCount'));
    }
}