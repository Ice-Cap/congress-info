<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('layouts.head', ['title' => $bill->title ?? 'Bill Details'])

<body>
    @include('layouts.header')
    @php
        $bill = (object) $bill;
    @endphp
    <main>
        <div class="bill-heading">
            <h1 class="heading-2">
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
        </div>
        @if (isset($aiSummary))
            <div class="bill-section ai-summary">
                <h2 class="heading-3">AI Summary</h2>
                <p>
                    @php
                        echo $aiSummary;
                    @endphp
                </p>
            </div>
        @endif
        @if (isset($summaries))
            <div class="bill-section">
                <h2 class="heading-3">Summaries</h2>
                @foreach ($summaries as $summary)
                    @php
                        $summary = trim($summary->text, '"');
                        if (strlen($summary) > 0) {
                            echo $summary;
                        } else {
                            echo 'No summary available';
                        }
                    @endphp
                @endforeach
                @if (count($summaries) === 0)
                    <p>No summaries available</p>
                @endif
            </div>
        @endif
        @if (isset($fullText))
            <div class="bill-section">
                <h2 class="heading-3">Full Text</h2>
                @php
                    echo $fullText;
                @endphp
            </div>
        @endif
    </main>
</body>

</html>
