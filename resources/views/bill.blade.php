<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Congress Info</title>
    </head>
    <body>
        @php
            $bill = (object)$bill;
        @endphp
        <main>
            <h1>
                {{ $bill->title }}
            </h1>
            <p>
                {{ $bill->number }}
            </p>
            <p>
                {{ $bill->updateDate }}
            </p>
            <h2>Summaries</h2>
            @foreach($summaries as $summary)
                @php
                    echo $summary = trim($summary["text"], '"');
                @endphp
            @endforeach
        </main>
    </body>
</html>