<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @vite(['resources/css/app.css'])
        <title>Congress Info</title>
    </head>
    <body>
        <header>
            <h1>Congress Info</h1>
            <nav>
                <a href="/">Home</a>
            </nav>
        </header>
        <main>
            @foreach($bills as $bill)
                @php
                    $bill = (object)$bill;
                @endphp
                <div class="bill">
                    <h4 class="heading-3">{{ $bill->title }}</h4>
                    <p>Number: {{ $bill->number }}</p>
                    <p>Latest action: {{ $bill->latestAction["text"] }} <span>Date: {{ $bill->latestAction["actionDate"] }}</span></p>
                    <a href="/bill/{{$bill->congress}}/{{$bill->type}}/{{$bill->number}}">View Bill</a>
                </div>
            @endforeach
        </main>
    </body>
</html>