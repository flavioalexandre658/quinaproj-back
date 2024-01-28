<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
class ImageHelper
{

    public static function storagePutB64Image(string $storageDirName, string $b64String): string
    {
        $extension = str_replace('image/', '.', mime_content_type($b64String));
        $fileName = time() . $extension;
        $fileString = preg_replace('#^data:image/\w+;base64,#i', '', $b64String);

        // Salve a imagem original
        Storage::put($storageDirName . $fileName, base64_decode($fileString));
        $storageDirName = str_replace('public/', 'storage/', $storageDirName);

        // Carregue a imagem original
        $imagePath = public_path($storageDirName . $fileName);
        $imagine = new Imagine();

        // Redimensione a imagem se necessário (opcional)
        $image = $imagine->open($imagePath);
        $image = $image->thumbnail(new Box(800, 800), ImageInterface::THUMBNAIL_OUTBOUND);
        $image->save($imagePath);

        // Converta a imagem para WebP
        $webpImagePath = $imagePath . '.webp';
        $imagine->open($imagePath)->save($webpImagePath, ['format' => 'webp']);


        // Remova a imagem temporária
        unlink($imagePath);

        return $storageDirName . $fileName. '.webp';
    }

    public static function deleteStorageFile(string $filePath): bool
    {
        return Storage::delete($filePath);
    }

    public static function updateStorageFile(string $filePath, array $data, string $storedFile): string
    {
        self::deleteStorageFile($storedFile);
        if (isset($data['image'])) {
            $imagePath = self::storagePutB64Image($filePath, $data['image']);
        } else {
            $imagePath = "";
        }
        return $imagePath;
    }

}
