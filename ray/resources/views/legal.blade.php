@extends('layouts.app')

@section('content')
<div class="fx-page">
    <div class="fx-head">
        <div class="fx-ic"><i class="ri-{{ $icon ?? 'article-line' }}"></i></div>
        <div>
            <h1>{{ $heading }}</h1>
            <div class="fx-sub">Last updated {{ $updated }}</div>
        </div>
        <div class="fx-spacer"></div>
        <a class="fx-btn ghost" href="/discover"><i class="ri-arrow-left-line"></i>Back to app</a>
    </div>

    <div class="fx-panel">
        @if (! empty($html))
            {!! $html !!}
        @else
            @foreach ($sections as $section)
                <h2>{{ $section['heading'] }}</h2>
                @foreach ($section['body'] as $paragraph)
                    <p class="legal-p">{{ $paragraph }}</p>
                @endforeach
            @endforeach
        @endif
    </div>
</div>
@endsection
