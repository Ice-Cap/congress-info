<?php

namespace App\Http\Controllers;

class CongressController extends Controller
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.congress.key');
    }
}
