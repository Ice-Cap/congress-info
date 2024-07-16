<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Http;

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

Route::get('/bill/{congress}/{billType}/{billNumber}', function ($congress, $billType, $billNumber) {
    $apiKey = config('services.congress.key');
    $billResponse = Http::get("https://api.congress.gov/v3/bill/$congress/$billType/$billNumber?api_key=$apiKey");
    if ($billResponse->failed()) {
        return Response::json([
            'error' => 'Failed to retrieve bill from Congress API'
        ], 500);
    }

    $summariesUrl = $billResponse["bill"]["summaries"]["url"] . "&api_key=$apiKey";
    $summariesResponse = Http::get($summariesUrl);
    if ($summariesResponse->failed()) {
        return Response::json([
            'error' => 'Failed to retrieve summaries from Congress API'
        ], 500);
    }

    return view('bill', [
        'bill' => (object)$billResponse['bill'],
        'summaries' => $summariesResponse['summaries']
    ]);
});
