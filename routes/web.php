<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;


Route::get('/', function () {
    return view('welcome');
});

Route::post('/chat', [ChatController::class, 'chat']); // Handles AI responses
//Route::post(url:'/chat', action:'App\Http\Controllers\ChatController'); // Handles AI responses