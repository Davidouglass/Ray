@extends('admin.layout')

@section('content')
    <div class="adm-page-head">
        <div>
            <div class="adm-title">Content</div>
            <div class="adm-subtitle">Edit legal and informational pages. Leave the body blank to use the built-in text.</div>
        </div>
    </div>

    @foreach ($pages as $key => $page)
        <div class="adm-card">
            <div class="adm-card-head"><i class="ri-article-line"></i> {{ ucfirst($key) }} <span class="adm-header-spacer"></span><a href="/{{ $key }}" target="_blank" rel="noopener" class="adm-btn sm"><i class="ri-external-link-line"></i> View page</a></div>
            <form method="POST" action="/admin/content/{{ $key }}" class="adm-card-body">
                @csrf
                <div class="adm-form">
                    <div class="adm-field">
                        <label>Title</label>
                        <input type="text" name="title" value="{{ old('title', $page->title) }}" required maxlength="140">
                    </div>
                    <div class="adm-field">
                        <label>Updated label</label>
                        <input type="text" name="updated_label" value="{{ old('updated_label', $page->updated_label) }}" maxlength="80">
                    </div>
                    <div class="adm-field full">
                        <label>Body (HTML — leave blank for default)</label>
                        <textarea name="body" class="adm-code" rows="12">{{ old('body', $page->body) }}</textarea>
                    </div>
                    <div class="adm-form-actions">
                        <button type="submit" class="adm-btn primary"><i class="ri-save-line"></i> Save page</button>
                    </div>
                </div>
            </form>
        </div>
    @endforeach
@endsection
