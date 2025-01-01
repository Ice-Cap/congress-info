<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css'])

    {{-- Allow additional meta tags to be injected --}}
    @isset($meta)
        {!! $meta !!}
    @endisset

    <title>
        @isset($title)
            {{ $title }} - Congress Info
        @else
            Congress Info
        @endisset
    </title>

    {{-- Allow additional head content to be injected --}}
    @stack('head')
</head>
