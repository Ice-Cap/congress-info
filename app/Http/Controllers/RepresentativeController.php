<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;

class RepresentativeController extends Controller
{
    public function index()
    {
        $apiKey = config('services.congress.key');
        $response = Http::get("https://api.congress.gov/v3/member?api_key=$apiKey&limit=10");
        if ($response->failed()) {
            return Response::json([
                'error' => 'Failed to retrieve representatives from Congress API'
            ], 500);
        }

        $data = $response->object();
        return view('representatives', ['representatives' => $data->members ?? []]);
    }

    public function show($id)
    {
        if (!$id) {
            return redirect('/representatives');
        }

        $apiKey = config('services.congress.key');
        $response = Http::get("https://api.congress.gov/v3/member/$id/?api_key=$apiKey");
        if ($response->failed()) {
            return Response::json([
                'error' => 'Failed to retrieve representative ' . $id . ' from Congress API'
            ], 500);
        }

        $data = $response->object();
        return view('rep', ['rep' => $data->member ?? null]);
    }

    public function bills($id)
    {
        $apiKey = config('services.congress.key');
        $sponsoredBills = Http::get("https://api.congress.gov/v3/member/$id/sponsored-legislation?api_key=$apiKey");
        $cosponsoredBills = Http::get("https://api.congress.gov/v3/member/$id/cosponsored-legislation?api_key=$apiKey");
        $rep = Http::get("https://api.congress.gov/v3/member/$id?api_key=$apiKey");
        return view('rep-bills', [
            'sponsoredBills' => $sponsoredBills->object()->sponsoredLegislation ?? [],
            'cosponsoredBills' => $cosponsoredBills->object()->cosponsoredLegislation ?? [],
            'rep' => $rep->object()->member ?? null
        ]);
    }
}
