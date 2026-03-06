<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Upload de vídeo (admin)
     */
    public function upload(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimetypes:video/mp4|max:102400', // 100MB max
            'title' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        try {
            // Salvar o arquivo
            $file = $request->file('video');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('public/uploads', $fileName);

            // URL pública do arquivo
            $url = Storage::url($path);

            // Criar o vídeo no banco
            $video = Video::create([
                'title' => $request->title,
                'description' => $request->description,
                'url' => $url,
                'thumbnail' => '',
                'duration' => '',
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vídeo enviado com sucesso!',
                'video' => [
                    'id' => $video->id,
                    'title' => $video->title,
                    'description' => $video->description,
                    'url' => $url,
                    'fileName' => $fileName
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao fazer upload: ' . $e->getMessage()
            ], 500);
        }
    }
}