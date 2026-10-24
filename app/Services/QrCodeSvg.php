<?php

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

final class QrCodeSvg
{
    public static function render(string $content): string
    {
        $options = new QROptions([
            'outputBase64' => false,
            'scale' => 6,
        ]);

        $svg = (new QRCode($options))->render($content);

        return is_string($svg) ? $svg : '';
    }
}
