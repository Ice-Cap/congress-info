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
                <p>Bill Number: {{ $bill->number }}</p>
                <p>Latest action: {{ $bill->latestAction['text'] }} <span>Date:
                        {{ $bill->latestAction['actionDate'] }}</span></p>
                <a href="/bill/{{ $bill->congress }}/{{ $bill->type }}/{{ $bill->number }}">View Bill</a>
            </div>
        @endforeach
        <!-- Handling pagination -->
        <div class="pagination">
            @php
                $limit = request()->query('limit', 20);
                $offset = request()->query('offset', 0);
                $prevPage = $offset - $limit;
                $nextPage = $offset + $limit;
                $page = $offset / $limit + 1;
            @endphp
            @if ($offset >= $limit)
                <a href="/?limit={{ $limit }}&offset={{ $prevPage }}">
                    < Previous Page</a>
            @endif
            <span>Page: {{ $page }}</span>
            <a href="/?limit={{ $limit }}&offset={{ $nextPage }}">Next Page ></a>
        </div>
    </main>
</body>

</html>
