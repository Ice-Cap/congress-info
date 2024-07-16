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
            <p>
                {{ $bill->title }}
            </p>
            <p>
                {{ $bill->number }}
            </p>
            <p>
                {{ $bill->updateDate }}
            </p>
        </main>
    </body>
</html>