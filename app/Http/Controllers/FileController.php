<?php

namespace App\Http\Controllers;

use App\Models\File;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function create(Request $request)
    {
        $url = "";
        try {
            $userSesion = $request->user();
            $idUserSesion = $userSesion->id;
            $name = $request->name;
            $modelName = $request->modelName;
            $modelId = $request->modelId;
            $type = $request->type;
            $file = $request->file;
            $extension = $request->extension;
            $storage = $request->storage;
            $state = $request->status;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $maintainFile = $request->maintain;
            $item = null;
            $f = base64_decode($file);

            // Crear un nombre aleatorio para la imagen
            $time = strtotime("now");
            $nameComplete = $name."-".$time.".".$extension;
            $path = "$modelId/$nameComplete";
            $url = "/storage/app/public/$storage/$path";

            Storage::disk($storage)->makeDirectory($modelId);
            $status = Storage::disk($storage)->put($path, $f);

            if (!$maintainFile) {
                File::where('name', $name)
                    ->where('model_id', $modelId)
                    ->where('model_name', $modelName)
                    ->delete();
            }

            $item = File::create([
                'name' => $name,
                'model_name' => $modelName,
                'model_id' => $modelId,
                'type' => $type,
                'extension' => $extension,
                'url' => $url,
                'registered_by' => $idUserSesion,
                'registered_date' => date('Y-m-d H:i:s'),
                'status' => $state,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => [
                [
                    'text' => $url,
                    'detail' => $status
                ]
            ],
            'data' => $item,
        ], Response::HTTP_OK);

    }

    public function get(Request $request)
    {
        try {
            $userSesion = $request->user();
            $idUserSesion = $userSesion->id;
            $name = $request->name;
            $modelName = $request->modelName;
            $modelId = $request->modelId;
            $item = File::where('name', $name)
                ->where('model_id', $modelId)
                ->where('model_name', $modelName)
                ->first();

        } catch (Exception $e) {
            return response()->json([
                'data' => [],
                'message'=>$e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $item,
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }

    function update(Request $request, int $id) {
        try {
            $item = File::find($id)->update($request->all());
        } catch (Exception $e) {
            return response()->json([
                'data' => [],
                'message'=>$e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $item,
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }

    public function delete(Request $request, $id)
    {
        try {
            $item = Image::find($id);
            Storage::disk('products')->delete($item->name);
            $items = Image::destroy($id);
        } catch (Exception $e) {
            return response()->json([
                'data' => [],
                'message'=>$e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $items,
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }

    public function downloadImageFromUrl(Request $request)
    {
        // Obtener el contenido de la imagen desde la URL
        $imageUrl = $request->query('imageUrl');
        $route = $request->query('route');
        $date = $request->query('date');

        // Validamos que la URL no esté vacía
        if (!$imageUrl) {
            return response()->json(['error' => 'URL de la imagen no proporcionada'], 400);
        }

        $imageContent = file_get_contents($imageUrl);

        // Verifica si se pudo obtener el contenido
        if ($imageContent === false) {
            return response()->json(['error' => 'No se pudo obtener la imagen'], 404);
        }

        // Extraer el nombre del archivo de la URL
        $fileName = basename($imageUrl);

        // Crear una respuesta de descarga con el contenido de la imagen
        return response($imageContent, 200)
            ->header('Content-Type', 'image/jpeg')  // Ajusta el tipo de contenido según el tipo de imagen
            ->header('Content-Disposition', 'attachment; filename="' . $route . ' - '. $date . ' - '. $fileName . '"');
    }
}
