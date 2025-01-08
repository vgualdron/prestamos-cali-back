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
                $sql = $this->zip->from('zips as z')
                            ->select(
                                'z.id',
                                'z.name',
                                'z.registered_by',
                                'u.name as registered_byname',
                                'z.registered_date',
                            )
                            ->leftJoin('users as u', 'z.registered_by', 'u.id')
                            ->get();

                if (count($sql) > 0){
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No hay zip para mostrar',
                                'detail' => 'Aun no ha exportado ningun comprimido de datos.'
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

                $path = storage_path("app/public/news");

                if (File::exists($path)) {
                    // Obtener todos los archivos y carpetas dentro del directorio
                    $files = File::files($path);
                    $directories = File::directories($path);

                    // Verificar si hay archivos o carpetas
                    if (count($files) > 0 || count($directories) > 0) {
                        $urlZip = $this->downloadZip('app/public');
                        $status = $this->zip::create([
                            'name' => $urlZip,
                            'registered_by' => $zip['registered_by'],
                            'registered_date' => date('Y-m-d H:i:s'),
                        ]);

                        // File::cleanDirectory($path);

                        return response()->json([
                            'message' => [
                                [
                                    'text' => 'Archivos exportados con éxito',
                                    'detail' => $urlZip,
                                ]
                            ]
                        ], Response::HTTP_OK);
                    } else {
                        return response()->json([
                            'message' => [
                                [
                                    'text' => 'No hay archivos para exportar',
                                    'detail' => 'Ya ha descargado todos los archivos anteriormente.',
                                ]
                            ]
                        ], Response::HTTP_NOT_FOUND);
                    }
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No hay archivos para exportar',
                                'detail' => 'Ya ha descargado todos los archivos anteriormente..',
                            ]
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }
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

        public function downloadZip($path) {
            // Directorio que quieres escanear
            $directory = storage_path("$path/news");

            // Nombre del archivo ZIP
            $time = date('d-m-Y-H-i-s');
            $zipFileName = "$time-archivos-de-los-clientes.zip";
            $zipRelativeName = "$path/zip/$zipFileName";
            $zipFilePath = storage_path($zipRelativeName);
            $pathClean = storage_path("$path/zip");
            // File::cleanDirectory($pathClean); activar para limpiar el folder

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

            return "/storage/$zipRelativeName";
        }

        private function addFilesToZip($zip, $directory, $baseDir = '') {
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
