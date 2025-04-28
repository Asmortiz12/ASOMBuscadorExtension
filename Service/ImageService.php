<?php

namespace FacturaScripts\Plugins\BuscadorExtension\Service;

use FacturaScripts\Core\Model\ProductoImagen;
use FacturaScripts\Core\Model\AttachedFile;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Base\MyFilesToken;

class ImageService
{
    public function getProductImages(int $productId): array
    {
        $productoImagenModel = new ProductoImagen();
        $attachedFileModel = new AttachedFile();

        // Buscar imágenes asociadas al producto
        $where = [new DataBaseWhere('idproducto', $productId)];
        $imagenes = $productoImagenModel->all($where, [], 0, 0);

        $images = [];
        foreach ($imagenes as $imagen) {
            // Obtener detalles del archivo adjunto
            $whereFile = [new DataBaseWhere('idfile', $imagen->idfile)];
            $file = $attachedFileModel->all($whereFile, [], 0, 1);

            if (count($file) > 0) {
                $file = $file[0];

                // Generar URL de la imagen
                $baseUrl = $this->getImageUrl($file->path);
                $token = MyFilesToken::get($file->path, true);
                $fileUrl = $baseUrl . '?myft=' . $token;

                $images[] = [
                    'id' => $imagen->id,
                    'filename' => $file->filename,
                    'url' => $fileUrl,
                    'mimetype' => $file->mimetype,
                ];
            }
        }

        return $images;
    }

    private function getImageUrl(string $path): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . $host . '/' . $path;
    }
}