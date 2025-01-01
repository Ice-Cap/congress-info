<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('layouts.head')

<body class="representative-page">
    @include('layouts.header')
    <main>
        <h2 class="heading-2">Representative {{ $rep->directOrderName }}</h2>
        @if ($rep)
            <div class="representative-info">
                <p>State: {{ $rep->state }}</p>
                <p>Cosponsored legislation: {{ $rep->cosponsoredLegislation->count }}</p>
                <p>Sponsored legislation: {{ $rep->sponsoredLegislation->count }}</p>
                <p>Party history:</p>
                <ul>
                    @foreach ($rep->partyHistory as $party)
                        <li>{{ $party->partyName }} - {{ $party->startYear }}</li>
                    @endforeach
                </ul>
            </div>
        @else
            <p>No representative found</p>
        @endif
    </main>
</body>

</html>
