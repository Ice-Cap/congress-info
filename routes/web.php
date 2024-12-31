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

/**
 * Get specific bill
 */
Route::get('/bill/{congress}/{billType}/{billNumber}', function ($congress, $billType, $billNumber)
{
    $apiKey = config('services.congress.key');
    $billResponse = Http::get("https://api.congress.gov/v3/bill/$congress/$billType/$billNumber?api_key=$apiKey");
    if ($billResponse->failed())
    {
        return Response::json([
            'error' => 'Failed to retrieve bill from Congress API'
        ], 500);
    }
    $billResponse = $billResponse->object();
    $bill = $billResponse->bill ?? null;
    if (!isset($bill))
    {
        return Response::json([
            'error' => 'Bill not found'
        ], 404);
    }

    $summaries = $bill->summaries ?? null;
    $summariesResponse = (object)[
        'summaries' => []
    ];
    if (!isset($summaries))
    {
        $summaries = [];
    }
    else
    {
        $summariesUrl = $billResponse->bill->summaries->url . "&api_key=$apiKey";
        $summariesResponse = Http::get($summariesUrl);
        if ($summariesResponse->failed())
        {
            $summariesResponse = (object)[
                'summaries' => []
            ];
        }
        else
        {
            $summariesResponse = $summariesResponse->object();
        }
    }

    $textVersionsUrl = $billResponse->bill->textVersions->url . "&api_key=$apiKey";
    $textResponse = Http::get($textVersionsUrl);
    $textFormatUrls = $textResponse->object()->textVersions[0]->formats;
    $textUrl = '';
    foreach ($textFormatUrls as $format)
    {
        if ($format->type === 'Formatted Text')
        {
            $textUrl = $format->url;
            break;
        }
    }

    if (!empty($textUrl))
    {
        $textResponse = Http::get($textUrl);
        if ($textResponse->failed())
        {
            $textResponse = '';
        }

        $textResponse = $textResponse->body();
    }
    else
    {
        $textResponse = null;
    }

    /**
     * If no text return bill without ai summary
     */
    if (empty($textResponse) || $textResponse === null)
    {
        return view('bill', [
            'bill' => $billResponse->bill,
            'summaries' => $summariesResponse->summaries
        ]);
    }

    /**
     * Get summary from chatgpt
     */
    $messages = [
        [
            'role' => 'system',
            'content' => 'You will take an image, document, or text of a congress bill and provide a short summary of it (try to stay around 350 words). Try to use simple, common language that can easily be understood by anyone. You must return the text as html only using p tags, ul tags, and ol tags. The first section of the summary should be one paragraph which is abrief, easy to understand summary of the bill no need to include the title in the summary. The second section will be short bullet points regarding the main points of the bill.'
        ],
        [
            'role' => 'user',
            'content' => 'Please summarize this bill: ' . $textResponse
        ]
    ];
    $openAiKey = config('services.openai.key');
    $summary = Http::withHeaders([
        'Authorization' => "Bearer $openAiKey",
        'Content-Type' => 'application/json'
    ])->post('https://api.openai.com/v1/chat/completions', [
        'model' => 'gpt-4o-mini',
        'messages' => $messages,
        'temperature' => 0.4,
    ]);
    if ($summary->failed())
    {
        echo '<pre>';
        print_r($summary->body());
        return Response::json([
            'error' => 'Failed to summarize bill'
        ], 500);
    }
    $summary = $summary->object();

    return view('bill', [
        'bill' => $billResponse->bill,
        'summaries' => $summariesResponse->summaries,
        'aiSummary' => $summary->choices[0]->message->content,
        'fullText' => $textResponse,
        'latestAction' => $billResponse->bill->latestAction
    ]);
});
