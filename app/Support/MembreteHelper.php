<?php

namespace App\Support;

class MembreteHelper
{
    public static function data(): array
    {
        $public = public_path('membrete');

        return [
            'headerImg' => self::base64("$public/image1.png"),
            'footerImg' => self::base64("$public/image2.png"),
            'direccion' => 'Barrio de Rameje, Villa Victoria, México. C.P. 50996',
            'escuela' => 'NIÑOS HEROES',
            'cct' => '15DPB0150E',
        ];
    }

    private static function base64(string $path): string
    {
        if (! file_exists($path)) {
            return '';
        }

        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $mime = match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            default => 'image/png',
        };

        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    }
}
