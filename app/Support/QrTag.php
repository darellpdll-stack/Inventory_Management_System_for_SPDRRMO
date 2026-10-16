<?php

namespace App\Support;

use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

class QrTag
{
    public static function svg(string $data, int $size = 110): string
    {
        $qrCode = new QrCode(
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Quartile,
            size: $size,
            margin: 0,
        );

        $result = (new SvgWriter())->write($qrCode, null, null, [
            SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true,
        ]);

        return $result->getString();
    }
}