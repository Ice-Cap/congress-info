<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\BillController;
use App\Http\Controllers\RepresentativeController;

/**
 * Home page
 *
 * returns a list of bills
 */
Route::get('/', function () {
    $apiKey = config('services.congress.key');
    $response = Http::get("https://api.congress.gov/v3/bill?api_key=$apiKey");
    if ($response->failed()) {
        return Response::json([
            'error' => 'Failed to retrieve bills from Congress API'
        ], 500);
    }

    return view('home', [
        'bills' => (object)$response['bills']
    ]);
});

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
