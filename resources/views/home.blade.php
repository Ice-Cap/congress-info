<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Congress Info</title>
    </head>
    <body>
        <main>
            @foreach($bills as $bill)
                @php
                    $bill = (object)$bill;
                @endphp
                <div>
                    <h4>{{ $bill->title }}</h4>
                    <p>{{ $bill->number }}</p>
                    <p>{{ $bill->updateDate }}</p>
                    <a href="/bill/{{$bill->congress}}/{{$bill->type}}/{{$bill->number}}">View Bill</a>
                </div>
            @endforeach
        </main>
    </body>
</html>