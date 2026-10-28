@extends('admin.layout')

@section('content')
    <div class="adm-page-head">
        <div>
            <div class="adm-title">Announcements</div>
            <div class="adm-subtitle">Banners shown to signed-in users across the app.</div>
        </div>
    </div>

    <div class="adm-card">
        <div class="adm-card-head"><i class="ri-add-line"></i> New announcement</div>
        <form method="POST" action="/admin/announcements" class="adm-card-body">
            @csrf
            <div class="adm-form">
                <div class="adm-field">
                    <label>Title (optional)</label>
                    <input type="text" name="title" value="{{ old('title') }}" maxlength="140">
                </div>
                <div class="adm-field">
                    <label>Type</label>
                    <select name="type">
                        @foreach (\App\Models\Announcement::TYPES as $type)
                            <option value="{{ $type }}" {{ old('type') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="adm-field full">
                    <label>Message</label>
                    <textarea name="message" required maxlength="600">{{ old('message') }}</textarea>
                </div>
                <div class="adm-field">
                    <label>Starts at (optional)</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}">
                </div>
                <div class="adm-field">
                    <label>Ends at (optional)</label>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}">
                </div>
                <div class="adm-field full">
                    <label class="adm-check"><input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}> Active</label>
                </div>
                <div class="adm-form-actions">
                    <button type="submit" class="adm-btn primary"><i class="ri-send-plane-line"></i> Publish</button>
                </div>
            </div>
        </form>
    </div>

    <div class="adm-card">
        <div class="adm-card-head"><i class="ri-megaphone-line"></i> All announcements</div>
        @if ($announcements->isEmpty())
            <div class="adm-empty">No announcements yet.</div>
        @else
            <div class="adm-list">
                @foreach ($announcements as $a)
                    <div class="adm-announce">
                        <div class="adm-announce-head">
                            <span class="adm-badge {{ $a->type === 'danger' ? 'danger' : ($a->type === 'success' ? 'ok' : ($a->type === 'warning' ? 'warn' : 'blue')) }}">{{ ucfirst($a->type) }}</span>
                            <b>{{ $a->title ?: 'Untitled' }}</b>
                            <span class="adm-header-spacer"></span>
                            <span class="adm-trader-meta">{{ optional($a->created_at)->diffForHumans() }}</span>
                        </div>
                        <p class="adm-announce-msg">{{ $a->message }}</p>
                        <div class="adm-inline">
                            <form method="POST" action="/admin/announcements/{{ $a->id }}/toggle">
                                @csrf
                                <button type="submit" class="adm-btn sm"><i class="ri-toggle-{{ $a->is_active ? 'fill' : 'line' }}"></i> {{ $a->is_active ? 'Active' : 'Inactive' }}</button>
                            </form>
                            <form method="POST" action="/admin/announcements/{{ $a->id }}" onsubmit="return confirm('Delete this announcement?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="adm-btn sm icon danger" title="Delete"><i class="ri-delete-bin-line"></i></button>
                            </form>
                        </div>
                        <form method="POST" action="/admin/announcements/{{ $a->id }}" class="adm-announce-edit">
                            @csrf
                            @method('PUT')
                            <div class="adm-form">
                                <div class="adm-field">
                                    <label>Title</label>
                                    <input type="text" name="title" value="{{ $a->title }}" maxlength="140">
                                </div>
                                <div class="adm-field">
                                    <label>Type</label>
                                    <select name="type">
                                        @foreach (\App\Models\Announcement::TYPES as $type)
                                            <option value="{{ $type }}" {{ $a->type === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="adm-field">
                                    <label>Starts</label>
                                    <input type="datetime-local" name="starts_at" value="{{ optional($a->starts_at)->format('Y-m-d\TH:i') }}">
                                </div>
                                <div class="adm-field">
                                    <label>Ends</label>
                                    <input type="datetime-local" name="ends_at" value="{{ optional($a->ends_at)->format('Y-m-d\TH:i') }}">
                                </div>
                                <div class="adm-field full">
                                    <label>Message</label>
                                    <textarea name="message" required maxlength="600">{{ $a->message }}</textarea>
                                </div>
                                <div class="adm-field full">
                                    <label class="adm-check"><input type="checkbox" name="is_active" value="1" {{ $a->is_active ? 'checked' : '' }}> Active</label>
                                </div>
                                <div class="adm-form-actions">
                                    <button type="submit" class="adm-btn"><i class="ri-save-line"></i> Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
            {{ $announcements->links('admin.pagination') }}
        @endif
    </div>
@endsection
