<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Util\Util;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BillController;

class SaveBills extends Command
{
    protected string $apiKey;
    protected BillController $billController;
    public function __construct()
    {
        parent::__construct();
        $this->apiKey = config('services.congress.key');
        $this->billController = new BillController();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:save-bills {offset} {limit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches bills from the Congress API and saves them to the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Start save bills");
        $this->saveBills();
    }

    public function saveBills()
    {
        $offset = $this->argument('offset');
        $limit = $this->argument('limit');
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
        $response = Http::get("https://api.congress.gov/v3/bill", $requestParams);

        if ($response->failed() || !isset($response['bills'])) {
            $this->error('Failed to retrieve bills from Congress API');
            return;
        }

        $bills = Util::toObject($response['bills']);
        if (!empty($bills)) {
            foreach ($bills as $bill) {
                $congress = $bill->congress ?? null;
                $billType = $bill->type ?? null;
                $billNumber = $bill->number ?? null;
                if (empty($congress) || empty($billType) || empty($billNumber)) {
                    continue;
                }

                $this->info("Checking if bill is cached: $congress/$billType/$billNumber");

                $cachedBill = $this->billController->getCachedBill($congress, $billType, $billNumber);
                if ($cachedBill) {
                    continue;
                }

                // sleep for 1 second to avoid rate limiting
                sleep(1);
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
                    $aiSummary = $this->billController->generateAISummary($textResponse);
                    $aiSummary = $aiSummary->choices[0]->message->content;
                }

                $this->info("Storing bill");
                $this->billController->storeBill($bill, $billNumber, $billType, $congress, $summariesResponse, $textResponse, $aiSummary);
            }
        }

        return view('home', [
            'bills' => $bills
        ]);
    }
}
