<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::latest()->get();
        return response()->json($videos);
    }

    public function show($id)
    {
        $video = Video::findOrFail($id);
        return response()->json($video);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'required|string',
            'thumbnail' => 'nullable|string',
            'duration' => 'nullable|string'
        ]);

        $video = Video::create([
            'title' => $request->title,
            'description' => $request->description,
            'url' => $request->url,
            'thumbnail' => $request->thumbnail,
            'duration' => $request->duration,
            'user_id' => auth()->id()
        ]);

        return response()->json($video, 201);
    }

    public function update(Request $request, $id)
    {
        $video = Video::findOrFail($id);
        $video->update($request->all());
        return response()->json($video);
    }

    public function destroy($id)
    {
        $video = Video::findOrFail($id);
        $video->delete();
        return response()->json(['message' => 'Vídeo deletado com sucesso']);
    }
}