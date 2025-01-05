<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\BillController;
use App\Http\Controllers\RepresentativeController;
use Illuminate\Http\Request;

/**
 * Home page
 *
 * returns a list of bills
 * Can filter with query params like: /?offset=20&limit=10
 */
Route::get('/', [BillController::class, 'getBillsList']);

/**
 * Get specific bill
 */
Route::get('/bill/{congress}/{billType}/{billNumber}', [BillController::class, 'show']);

/**
 * Get all representatives
 */
Route::get('/representatives', [RepresentativeController::class, 'index']);

/**
 * Get specific representative
 */
Route::get('/representative/{id}', [RepresentativeController::class, 'show']);

/**
 * Get bills for a specific representative
 */
Route::get('/representative/{id}/bills', [RepresentativeController::class, 'bills']);
