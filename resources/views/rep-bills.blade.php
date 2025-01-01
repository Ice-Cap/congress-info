<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('layouts.head')

<body class="representative-bills-page">
    @include('layouts.header')
    <main>
        <h2 class="heading-2">Representative {{ $rep->directOrderName }} - Bills</h2>
        <div class="image-container">
            <img src="{{ $rep->depiction->imageUrl }}" alt="{{ $rep->directOrderName }}">
        </div>
        <div class="bills-container">
            <h3 class="heading-3">Sponsored Bills</h3>
            @foreach ($sponsoredBills as $bill)
                <div class="bill">
                    <h4 class="heading-4">{{ $bill->title ?? 'N/A' }}</h4>
                    <p>Policy area: {{ $bill->policyArea->name ?? 'N/A' }}</p>
                    <p>Congress: {{ $bill->congress ?? 'N/A' }}</p>
                    <p>Introduced: {{ $bill->introducedDate ?? 'N/A' }}</p>
                    <p>Last action: {{ $bill->latestAction->actionDate ?? 'N/A' }} -
                        {{ $bill->latestAction->text ?? 'N/A' }}</p>
                    @if ($bill->congress && $bill->type && $bill->number)
                        <a href="/bill/{{ $bill->congress }}/{{ $bill->type }}/{{ $bill->number }}"
                            class="btn">View
                            Bill</a>
                    @endif
                </div>
            @endforeach
            <h3 class="heading-3">Cosponsored Bills</h3>
            @foreach ($cosponsoredBills as $bill)
                <div class="bill">
                    <h4 class="heading-4">{{ $bill->title ?? 'N/A' }}</h4>
                    <p>Policy area: {{ $bill->policyArea->name ?? 'N/A' }}</p>
                    <p>Congress: {{ $bill->congress ?? 'N/A' }}</p>
                    <p>Introduced: {{ $bill->introducedDate ?? 'N/A' }}</p>
                    <p>Last action: {{ $bill->latestAction->actionDate ?? 'N/A' }} -
                        {{ $bill->latestAction->text ?? 'N/A' }}</p>
                    @if ($bill->congress && $bill->type && $bill->number)
                        <a href="/bill/{{ $bill->congress }}/{{ $bill->type }}/{{ $bill->number }}"
                            class="btn">View
                            Bill</a>
                    @endif
                </div>
            @endforeach
        </div>
    </main>
</body>

</html>
