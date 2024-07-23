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
        @php
            $bill = (object)$bill;
        @endphp
        <main>
            <h1>
                {{ $bill->title }}
            </h1>
            <p>
                Bill Number: {{ $bill->number }}
            </p>
            <p>
                Last Updated: {{ $bill->updateDate }}
            </p>
            <p>
                Latest Action: {{ $bill->latestAction->text }} <span>Date: {{ $bill->latestAction->actionDate }}</span>
            </p>
            <h2>AI Summary</h2>
            <p>
                @php
                    echo $aiSummary;
                @endphp
            </p>
            <h2>Summaries</h2>
            @foreach($summaries as $summary)
                @php
                    echo $summary = trim($summary->text, '"');
                @endphp
            @endforeach
            <h2>Full Text</h2>
            @php
                echo $fullText;
            @endphp
        </main>
    </body>
</html>