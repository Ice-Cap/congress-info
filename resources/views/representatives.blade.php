<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('layouts.head')

<body class="representatives-page">
    @include('layouts.header')
    <main>
        <h2 class="heading-2">Representatives</h2>
        @if (empty($representatives))
            <p>No representatives found.</p>
        @else
            @foreach ($representatives as $representative)
                <div class="representative flex-between">
                    <div class="image-container">
                        @if (isset($representative->depiction))
                            <a href="/representative/{{ $representative->bioguideId }}">
                                <img src="{{ $representative->depiction->imageUrl }}" alt="{{ $representative->name }}">
                            </a>
                        @endif
                    </div>
                    <div class="info-container">
                        <h4 class="heading-4">{{ $representative->name }}</h4>
                        <p>Party: {{ $representative->partyName }}</p>
                        <p>State: {{ $representative->state }}</p>
                    </div>
                    <div class="btn-container flex-column">
                        <a href="/representative/{{ $representative->bioguideId }}" class="btn">View Details</a>
                        <a href="/representative/{{ $representative->bioguideId }}/bills" class="btn">View Bills</a>
                    </div>
                </div>
            @endforeach
        @endif
    </main>
</body>

</html>
