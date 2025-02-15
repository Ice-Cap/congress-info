<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Util\Util;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BillController;
use Illuminate\Support\Benchmark;

class SaveBills extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:save-bills {offset} {limit} {congress?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches bills from the Congress API and saves them to the database';

    protected string $apiKey;
    protected BillController $billController;

    protected int $currentCongress = 119;
    protected int $billsSaved = 0;

    public function __construct()
    {
        parent::__construct();
        $this->apiKey = config('services.congress.key');
        $this->billController = new BillController();

        // Set script timeout to 1 hour
        set_time_limit(3600);

        // Increase memory limit to 512MB
        ini_set('memory_limit', '512M');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Start save bills");

        $executionTime = Benchmark::measure(function () {
            $this->saveBills();
        });

        $seconds = number_format($executionTime / 1000, 2);
        $minutes = number_format($seconds / 60, 2);
        $this->info("Bills saved: {$this->billsSaved}");
        $this->info("Execution time: {$seconds} seconds or {$minutes} minutes");
    }

    public function saveBills()
    {
        $offset = $this->argument('offset');
        $limit = $this->argument('limit');
        $congress = $this->argument('congress') ?? $this->currentCongress;
        $startDate = null;
        $endDate = null;
        $sort = null;

        $requestParams = [
            'api_key' => $this->apiKey,
            'offset' => $offset,
            'limit' => $limit,
        ];
        $optionalParams = [
            'fromDateTime' => $startDate,
            'toDateTime' => $endDate,
            'sort' => $sort
        ];
        foreach ($optionalParams as $key => $val) {
            if (!$val) {
                continue;
            }

            $requestParams[$key] = $val;
        }
        $response = Http::get("https://api.congress.gov/v3/bill/{$congress}", $requestParams);

        if ($response->failed() || !isset($response['bills'])) {
            $this->error('Failed to retrieve bills from Congress API');
            return;
        }

        $bills = Util::toObject($response['bills']);
        if (empty($bills)) {
            $this->fail("Unable to convert bills to an object");
            return;
        }

        foreach ($bills as $bill) {
            $congress = $bill->congress ?? null;
            $billType = $bill->type ?? null;
            $billNumber = $bill->number ?? null;
            if (empty($congress) || empty($billType) || empty($billNumber)) {
                continue;
            }

            $this->info("Checking if bill is cached: $congress/$billType/$billNumber");

            $cachedBill = $this->billController->getStoredBill($congress, $billType, $billNumber);

            // Check if bill has been updated since last stored
            $hasBeenUpdated = $this->checkWasUpdated($cachedBill, $bill);
            if ($hasBeenUpdated) {
                $this->info("Bill has been updated since last saved. Updating information");
            }

            if ($cachedBill && !$hasBeenUpdated) {
                $this->info("Bill already saved and hasn't been updated since last run. Skipping bill");
                continue;
            }

            $this->info("Getting bill from Congress API: $congress/$billType/$billNumber");
            // sleep for 0.4 seconds to avoid rate limiting
            usleep(400000);  // 400000 microseconds = 0.4 seconds
            $billResponse = Http::get("https://api.congress.gov/v3/bill/$congress/$billType/$billNumber?api_key=" . $this->apiKey);

            if ($billResponse->failed()) {
                $this->error('Failed to retrieve bill from Congress API');
                return;
            }

            $billResponse = $billResponse->object();
            $bill = $billResponse->bill ?? null;
            if (!isset($bill)) {
                $this->error("Bill not found: $congress/$billType/$billNumber");
                return;
            }

            $this->info("Got bill: $congress/$billType/$billNumber");

            $this->info("Getting bill summaries");
            $summariesResponse = $this->billController->getBillSummaries($bill, $this->apiKey);

            $this->info("Getting bill text");
            $textResponse = $this->billController->getBillText($bill, $this->apiKey) ?? '';

            $aiSummary = '';
            if (!empty($textResponse)) {
                $this->info("Generating AI summary");
                $aiSummaryResponse = $this->billController->generateAISummary($textResponse);
                if ($aiSummaryResponse->failed()) {
                    $this->error("Failed to generate AI summary response: $aiSummaryResponse");
                    return;
                }

                $aiSummary = $aiSummaryResponse->object();
                if (!isset($aiSummary->choices[0]->message->content)) {
                    $this->error("Failed to generate AI summary response: $aiSummary");
                    return;
                }

                $aiSummary = $aiSummary->choices[0]->message->content;
            }

            $this->info("Storing bill");
            $this->billsSaved += 1;
            $this->billController->storeBill($bill, $billNumber, $billType, $congress, $summariesResponse, $textResponse, $aiSummary);
        }
    }

    protected function checkWasUpdated(?object $cachedBill, object $bill): bool
    {
        $hasBeenUpdated = false;
        if (!empty($cachedBill)) {
            $hadNewAction = strtotime($cachedBill->bill_latest_action_date) < strtotime($bill->latestAction->actionDate);
            $wasUpdated = strtotime($cachedBill->bill_update_date) < strtotime($bill->updateDateIncludingText);
            $hasBeenUpdated = $hadNewAction || $wasUpdated;
        }

        return $hasBeenUpdated;
    }
}
