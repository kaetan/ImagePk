<?php

namespace ImagePk;

class ImagePk
{
    //TODO: add finfo and GD to composer.json

    private $allowedTypes = [
        'image/jpeg',
        'image/jpg',
        'image/png'
    ];

    public function applyWatermark(string $imagePath, string $watermarkPath, string $resultPath)
    {
        $mainImage = $this->createFromFile($imagePath);
        $watermarkImage = $this->createFromFile($watermarkPath);
        $markWidth = 200;
        $markHeight = 50;
        $margin = 10;

        $watermarkResized = imagescale($watermarkImage, $markWidth, $markHeight);
        imagecopymerge(
            $mainImage,
            $watermarkResized,
            imagesx($mainImage) - $markWidth - $margin,
            imagesy($mainImage) - $markHeight - $margin,
            0,
            0,
            $markWidth,
            $markHeight,
            100
        );

        imagepng($mainImage, $resultPath . 'imageMarked-' . rand(0, 99999999) . '.png');
        imagedestroy($mainImage);
    }

    private function createFromFile(string $filePath)
    {
        $image = null;

        $this->validateType($filePath);

        $filePathParts = pathinfo($filePath);
        $fileExtension = $filePathParts['extension'];

        if (!$fileExtension) {
            throw new \Exception('Файл не найден или у файла отсутствует расширение.');
        }

        $method = null;
        switch ($fileExtension) {
            case 'jpeg':
            case 'jpg':
                $method = 'imagecreatefromjpeg';
                break;
            case 'png':
                $method = 'imagecreatefrompng';
                break;
        }

        if ($method && function_exists($method)) {
            $image = $method($filePath);
        }

        return $image;
    }

    private function validateType(string $filePath)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileInfo = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        if (in_array($fileInfo, $this->allowedTypes) == false) {
            throw new \Exception('Неподходящий формат файла.');
        }
    }
}
