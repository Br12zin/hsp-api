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
        try {
            // Validação
            $request->validate([
                'video' => 'required|file|mimetypes:video/mp4|max:102400',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string'
            ]);

            // Pega o arquivo
            $file = $request->file('video');
            
            // Gera nome único (mantém extensão original)
            $extension = $file->getClientOriginalExtension();
            $fileName = time() . '_' . uniqid() . '.' . $extension;
            
            // 🔥 CAMINHO CORRETO: storage/app/public/uploads
            $destinationPath = storage_path('app/public/uploads');
            
            // Cria a pasta se não existir
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            
            // Move o arquivo
            $file->move($destinationPath, $fileName);
            
            // URL pública (via storage link)
            $url = '/storage/uploads/' . $fileName;

            // Salva no banco
            $video = Video::create([
                'title' => $request->title,
                'description' => $request->description ?? '',
                'url' => $url,
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

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}