<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use Illuminate\Http\Request;

class StreamsController extends Controller
{
    public function index()
    {
        $user = request()->user();

        $live = Stream::where('status', 'live')->with('user')->orderByDesc('viewers')->get();
        $mine = $user->streams()->latest()->limit(12)->get();

        return view('streams', [
            'title'    => 'Streams',
            'active'   => 'streams',
            'live'     => $live,
            'mine'     => $mine,
            'liveCount' => $live->count(),
            'viewers'  => (int) $live->sum('viewers'),
        ]);
    }

    public function create()
    {
        return view('stream-create', [
            'title'  => 'Go live',
            'active' => 'streams',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'         => ['required', 'string', 'max:140'],
            'description'   => ['nullable', 'string', 'max:500'],
            'token_address' => ['nullable', 'string', 'max:120'],
            'token_symbol'  => ['nullable', 'string', 'max:40'],
            'source'        => ['required', 'in:camera,embed'],
            'embed_url'     => ['nullable', 'url', 'max:500', 'required_if:source,embed'],
        ]);

        $user = $request->user();

        // Only one live stream at a time per user.
        $user->streams()->where('status', 'live')->update(['status' => 'ended', 'ended_at' => now()]);

        $stream = $user->streams()->create([
            'title'         => $data['title'],
            'description'   => $data['description'] ?? null,
            'token_address' => $data['token_address'] ?? null,
            'token_symbol'  => $data['token_symbol'] ?? null,
            'source'        => $data['source'],
            'embed_url'     => $data['source'] === 'embed' ? ($data['embed_url'] ?? null) : null,
            'status'        => 'live',
            'viewers'       => 0,
            'started_at'    => now(),
        ]);

        return redirect('/streams/' . $stream->id)->with('ok', 'You are live.');
    }

    public function show(Stream $stream)
    {
        $isOwner = $stream->user_id === optional(request()->user())->id;

        if (! $isOwner && $stream->isLive()) {
            $stream->increment('viewers');
            $stream->refresh();
        }

        return view('stream', [
            'title'   => $stream->title . ' · Streams',
            'active'  => 'streams',
            'stream'  => $stream,
            'isOwner' => $isOwner,
        ]);
    }

    public function end(Request $request, Stream $stream)
    {
        abort_unless($stream->user_id === $request->user()->id, 403);

        if ($stream->isLive()) {
            $stream->update(['status' => 'ended', 'ended_at' => now()]);
        }

        return redirect('/streams')->with('ok', 'Stream ended.');
    }
}
