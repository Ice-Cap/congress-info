<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Util\Util;
use stdClass;

class BillController extends CongressController
{
   public function getBillsList(Request $request)
   {
      $offset = $request->query('offset', 0);
      $limit = $request->query('limit', 20);
      $cacheKey = "bills-list-$offset-$limit";
      $cacheTTL = 1200;

      if (Cache::has($cacheKey)) {
         $bills = Cache::get($cacheKey);
         $bills = json_decode($bills);
      } else {
         $response = Http::get("https://api.congress.gov/v3/bill", [
            'api_key' => $this->apiKey,
            'offset' => $offset,
            'limit' => $limit,
         ]);

         if ($response->failed() || !isset($response['bills'])) {
            return Response::json([
               'error' => 'Failed to retrieve bills from Congress API'
            ], 500);
         }

         $cachedBills = json_encode(Util::toObject($response['bills']));
         Cache::put($cacheKey, $cachedBills, $cacheTTL);

         $bills = Util::toObject($response['bills']);
      }


      return view('home', [
         'bills' => $bills
      ]);
   }

   public function show(string $congress, string $billType, string $billNumber): View|JsonResponse
   {
      // Check if bill exists in database
      $cachedBill = $this->getCachedBill($congress, $billType, $billNumber);

      if ($cachedBill) {
         return view('bill', [
            'bill' => (object)[
               'title' => $cachedBill->bill_title,
               'number' => $cachedBill->bill_id,
               'updateDate' => $cachedBill->bill_update_date,
               'latestAction' => (object)[
                  'text' => $cachedBill->bill_latest_action,
                  'actionDate' => $cachedBill->bill_latest_action_date
               ]
            ],
            'summaries' => json_decode($cachedBill->bill_summary),
            'aiSummary' => $cachedBill->bill_ai_summary,
            'fullText' => $cachedBill->bill_full_text,
            'latestAction' => (object)[
               'text' => $cachedBill->bill_latest_action,
               'actionDate' => $cachedBill->bill_latest_action_date
            ],
            'sponsors' => json_decode($cachedBill->bill_sponsors) ?? []
         ]);
      }

      $billResponse = Http::get("https://api.congress.gov/v3/bill/$congress/$billType/$billNumber?api_key=$this->apiKey");

      if ($billResponse->failed()) {
         return Response::json([
            'error' => 'Failed to retrieve bill from Congress API'
         ], 500);
      }

      $billResponse = $billResponse->object();
      $bill = $billResponse->bill ?? null;

      if (!isset($bill)) {
         return Response::json([
            'error' => 'Bill not found'
         ], 404);
      }

      $summariesResponse = $this->getBillSummaries($bill, $this->apiKey);
      $textResponse = $this->getBillText($bill, $this->apiKey);

      if (empty($textResponse)) {
         return view('bill', [
            'bill' => $billResponse->bill,
            'summaries' => $summariesResponse->summaries
         ]);
      }

      $aiSummary = $this->generateAISummary($textResponse);

      if (!$aiSummary) {
         return Response::json([
            'error' => 'Failed to summarize bill'
         ], 500);
      }

      // Store in database
      $this->storeBill($billResponse->bill, $billNumber, $billType, $congress, $summariesResponse, $textResponse, $aiSummary);

      return view('bill', [
         'bill' => $billResponse->bill,
         'summaries' => $summariesResponse->summaries,
         'aiSummary' => $aiSummary->choices[0]->message->content,
         'fullText' => $textResponse,
         'latestAction' => $billResponse->bill->latestAction,
         'sponsors' => $billResponse->bill->sponsors ?? []
      ]);
   }

   private function getCachedBill(string $congress, string $billType, string $billNumber): ?stdClass
   {
      return DB::table('bills')
         ->where('congress_number', $congress)
         ->where('bill_type', $billType)
         ->where('bill_id', $billNumber)
         ->first();
   }

   private function getBillSummaries(stdClass $bill, string $apiKey): stdClass
   {
      $summaries = $bill->summaries ?? null;
      if (!isset($summaries)) {
         return (object)['summaries' => []];
      }

      $summariesUrl = $bill->summaries->url . "&api_key=$apiKey";
      $summariesResponse = Http::get($summariesUrl);

      if ($summariesResponse->failed()) {
         return (object)['summaries' => []];
      }

      return $summariesResponse->object();
   }

   private function getBillText(stdClass $bill, string $apiKey): ?string
   {
      $textVersions = $bill->textVersions ?? null;
      if (!isset($textVersions)) {
         return null;
      }

      $textVersionsUrl = $bill->textVersions->url . "&api_key=$apiKey";
      $textResponse = Http::get($textVersionsUrl);
      $textFormatUrls = $textResponse->object()->textVersions[0]->formats;

      $textUrl = collect($textFormatUrls)
         ->firstWhere('type', 'Formatted Text')
         ?->url;

      if (empty($textUrl)) {
         return null;
      }

      $textResponse = Http::get($textUrl);
      return $textResponse->failed() ? null : $textResponse->body();
   }

   private function generateAISummary(string $textResponse): ?stdClass
   {
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

      return Http::withHeaders([
         'Authorization' => "Bearer " . config('services.openai.key'),
         'Content-Type' => 'application/json'
      ])->post('https://api.openai.com/v1/chat/completions', [
         'model' => 'gpt-4o-mini',
         'messages' => $messages,
         'temperature' => 0.4,
      ])->object();
   }

   private function storeBill(stdClass $bill, string $billNumber, string $billType, string $congress, stdClass $summariesResponse, string $textResponse, stdClass $aiSummary): void
   {
      $sponsors = $bill->sponsors ?? [];
      $sponsors = json_encode($sponsors);
      DB::table('bills')->insert([
         'bill_id' => $billNumber,
         'bill_type' => $billType,
         'congress_number' => $congress,
         'bill_summary' => json_encode($summariesResponse->summaries),
         'bill_title' => $bill->title,
         'bill_ai_summary' => $aiSummary->choices[0]->message->content,
         'bill_full_text' => $textResponse,
         'bill_latest_action' => $bill->latestAction->text,
         'bill_latest_action_date' => $bill->latestAction->actionDate,
         'bill_update_date' => $bill->updateDate,
         'bill_sponsors' => $sponsors,
         'created_at' => now(),
         'updated_at' => now()
      ]);
   }
}
