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
 * Returns a list of bills
 * Can filter with query params
 * Query params: offset, limit, fromDate, toDate, sort
 */
Route::get('/', [BillController::class, 'getBillsList']);

/**
 * Get specific bill
 */
Route::get('/bill/{congress}/{billType}/{billNumber}', [BillController::class, 'show']);

/**
 * Search bills
 */
Route::get('/bills/search', [BillController::class, 'search']);

/**
 * Get stored bills
 */
Route::get('/stored-bills', [BillController::class, 'getStoredBills']);

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
