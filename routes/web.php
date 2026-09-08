<?php

use App\Http\Controllers\BudgetController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BudgetController::class, 'index'])->name('home');
Route::post('/calculate', [BudgetController::class, 'calculate'])->name('budget.calculate');
