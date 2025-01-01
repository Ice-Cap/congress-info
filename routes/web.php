<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BillController;

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