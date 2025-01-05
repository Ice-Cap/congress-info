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
Route::get('/', function (Request $request) {
    $apiKey = config('services.congress.key');
    $offset = $request->query('offset', 0);
    $limit = $request->query('limit', 20);

    $response = Http::get("https://api.congress.gov/v3/bill", [
        'api_key' => $apiKey,
        'offset' => $offset,
        'limit' => $limit,
    ]);

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

/**
 * Get bills for a specific representative
 */
Route::get('/representative/{id}/bills', [RepresentativeController::class, 'bills']);
