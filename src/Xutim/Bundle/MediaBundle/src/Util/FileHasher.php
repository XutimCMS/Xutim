<?php

declare(strict_types=1);

namespace Xutim\MediaBundle\Util;

class FileHasher
{
    public static function genereatePerceptualHash(string $path): string
    {
        $size = 8;
        $imgString = file_get_contents($path);
        if ($imgString === false) {
            throw new \Exception('Invalid image path.');
        }

        $img = imagecreatefromstring($imgString);
        if ($img === false) {
            throw new \Exception('Invalid image.');
        }

        $resized = imagecreatetruecolor($size, $size);
        imagecopyresampled($resized, $img, 0, 0, 0, 0, $size, $size, imagesx($img), imagesy($img));
        unset($img);

        $grayscale = [];
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $rgb = imagecolorat($resized, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $gray = ($r + $g + $b) / 3;
                $grayscale[] = $gray;
            }
        }
        unset($resized);

        $average = array_sum($grayscale) / count($grayscale);

        $bits = '';
        foreach ($grayscale as $gray) {
            $bits .= ($gray >= $average) ? '1' : '0';
        }

        return base_convert($bits, 2, 16);
    }

    public static function generateSHA256Hash(string $path): string
    {
        $hash = hash_file('sha256', $path);
        if ($hash === false) {
            throw new \Exception('Invalid file.');
        }

        return $hash;
    }
}
