<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('layouts.head')

<body>
    @include('layouts.header')
    <main>
        <h2 class="heading-2">Recent Bills</h2>
        @foreach ($bills as $bill)
            @php
                $bill = (object) $bill;
            @endphp
            <div class="bill">
                <h4 class="heading-4">{{ $bill->title }}</h4>
                <p>Number: {{ $bill->number }}</p>
                <p>Latest action: {{ $bill->latestAction['text'] }} <span>Date:
                        {{ $bill->latestAction['actionDate'] }}</span></p>
                <a href="/bill/{{ $bill->congress }}/{{ $bill->type }}/{{ $bill->number }}">View Bill</a>
            </div>
        @endforeach
    </main>
</body>

</html>
