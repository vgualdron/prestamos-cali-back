<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\ZipServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Zip;
    use App\Validator\ZipValidator;
    use App\Traits\Commons;
    use ZipArchive;
    use File;
    
    class ZipServiceImplement implements ZipServiceInterface {

        use Commons;

        private $zip;
        private $validator;

        function __construct(ZipValidator $validator){
            $this->zip = new Zip;
            $this->validator = $validator;
        }    

        function list(){
            try {
                $sql = $this->zip->select(
                    'id',
                    'name',
                    'registered_by',
                    'registered_date',
                )->get();

                if (count($sql) > 0){
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No hay zip para mostrar',
                                'detail' => 'Aun no ha registrado ningun zip'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar lo zips',
                            'detail' => 'intente recargando la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }


        function create(array $zip) {
            try {
                $validation = $this->validate($this->validator, $zip, null, 'registrar', 'zip', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }

                $this->downloadZip('app/public/news');

                $status = $this->zip::create([
                    'name' => 'name',
                    'registered_by' => $zip['registered_by'],
                    'registered_date' => date('Y-m-d H:i:s'),
                ]);
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Zip registrada con éxito',
                            'detail' => null
                        ]
                    ]
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al registrar el zip',
                            'detail' => $e->getMessage(),
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        public function downloadZip($path)
        {
            // Directorio que quieres escanear
            $directory = storage_path($path);
    
            // Nombre del archivo ZIP
            $zipFileName = 'files.zip';
            $zipFilePath = storage_path($zipFileName);
    
            // Crear una instancia de ZipArchive
            $zip = new ZipArchive();
    
            // Abrir el archivo ZIP para escribir
            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
                return response()->json(['error' => 'No se puede crear el archivo ZIP'], 500);
            }
    
            // Escanear el directorio y agregar archivos al archivo ZIP
            $this->addFilesToZip($zip, $directory);
    
            // Cerrar el archivo ZIP
            $zip->close();
    
            // Configurar encabezados para la descarga del archivo ZIP
            return response()->download($zipFilePath)->deleteFileAfterSend(true);
        }
    
        private function addFilesToZip($zip, $directory, $baseDir = '')
        {
            $files = File::allFiles($directory);
            foreach ($files as $file) {
                $relativePath = $baseDir . $file->getRelativePathname();
                $zip->addFile($file->getRealPath(), $relativePath);
            }
    
            $directories = File::directories($directory);
            foreach ($directories as $dir) {
                $this->addFilesToZip($zip, $dir, $baseDir . basename($dir) . '/');
            }
        }
    }
?>