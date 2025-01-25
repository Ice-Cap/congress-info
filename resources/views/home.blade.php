<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('layouts.head')

<body>
    @include('layouts.header')
    <main>
        <h2 class="heading-2">Recent Bills</h2>
        <div class="page-filters">
            @php
                $startDate = request()->query('startDate', null);
                $endDate = request()->query('endDate', null);
                $sort = request()->query('sort', '');
            @endphp
            <form action="/" id="page-filter-form">
                <div class="input-group">
                    <label>Start date:</label>
                    <input type="date" id="start-date" name="startDate" value="{{ $startDate }}">
                </div>
                <div class="input-group">
                    <label>End date:</label>
                    <input type="date" id="end-date" name="endDate" value="{{ $endDate }}">
                </div>
                <div class="input-group">
                    <label>Sort:</label>
                    <select name="sort" id="page-sort">
                        <option value="" disabled {{ $sort == '' ? 'selected' : '' }}>Select a sort option
                        </option>
                        <option value="asc" {{ $sort == 'asc' ? 'selected' : '' }}>Last Updated Ascending</option>
                        <option value="desc" {{ $sort == 'desc' ? 'selected' : '' }}>Last Updated Descending</option>
                    </select>
                </div>
                <div class="submit-container">
                    <button type="submit" class="btn btn-primary">
                        Filter
                    </button>
                </div>
            </form>
        </div>
        @foreach ($bills as $bill)
            @php
                $bill = (object) $bill;
            @endphp
            <div class="bill">
                <h4 class="heading-4">{{ $bill->title }}</h4>
                <p>Bill Number: {{ $bill->number }}</p>
                <p>Bill Type: {{ $bill->type }}</p>
                <p>Congress: {{ $bill->congress }}</p>
                <p>Latest action: {{ $bill->latestAction->text }} <span>Date:
                        {{ $bill->latestAction->actionDate }}</span></p>
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

                $baseUrl = "/?limit=$limit";
                if ($startDate) {
                    $baseUrl .= "&startDate=$startDate";
                }
                if ($endDate) {
                    $baseUrl .= "&endDate=$endDate";
                }
                if ($sort) {
                    $baseUrl .= "&sort=$sort";
                }
                $prevUrl = "$baseUrl&offset=$prevPage";
                $nextUrl = "$baseUrl&offset=$nextPage";
            @endphp
            @if ($offset >= $limit)
                <a href="{{ $prevUrl }}">
                    < Previous Page</a>
            @endif
            <span>Page: {{ $page }}</span>
            <a href="{{ $nextUrl }}">Next Page ></a>
        </div>
    </main>

    <script>
        const filterForm = document.getElementById('page-filter-form');

        function handleFilterSubmit(e) {
            e.preventDefault();

            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);

            // Get current URL and update query parameters
            const url = new URL(window.location.href);
            for (const [key, value] of params) {
                if (value) {
                    url.searchParams.set(key, value);
                } else {
                    url.searchParams.delete(key);
                }
            }

            // Reset offset when filter changes
            url.searchParams.set('offset', '0');

            // Navigate to filtered URL
            window.location.href = url.toString();
        }

        // Add event listener to form
        filterForm.addEventListener('submit', handleFilterSubmit);
    </script>
</body>

</html>
