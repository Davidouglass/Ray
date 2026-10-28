@php
    $announcements = $announcements ?? \App\Models\Announcement::running()->orderByDesc('id')->take(3)->get();
@endphp
@if ($announcements->isNotEmpty())
    <div class="ann-bar-stack">
        @foreach ($announcements as $a)
            <div class="ann-bar ann-{{ $a->type }}">
                <i class="ri-{{ $a->type === 'danger' ? 'error-warning-line' : ($a->type === 'warning' ? 'alert-line' : ($a->type === 'success' ? 'checkbox-circle-line' : 'information-line')) }}"></i>
                <div class="ann-bar-body">
                    @if ($a->title)<b>{{ $a->title }}</b>@endif
                    <span>{{ $a->message }}</span>
                </div>
            </div>
        @endforeach
    </div>
@endif
