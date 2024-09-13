<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/',[ProductController::class,'index'])->name('home');
Route::post('/create-checkout-session',[ProductController::class,'checkout'])->name('checkout');
Route::get('/checkout-success',[ProductController::class,'success'])->name('checkout.success');
Route::get('/checkout-cancel',[ProductController::class,'cancel'])->name('checkout.cancel');
Route::post('/webhook',[ProductController::class,'webhook'])->name('webhook');