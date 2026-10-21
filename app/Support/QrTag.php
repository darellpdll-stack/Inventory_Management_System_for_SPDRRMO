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

    /**
     * What a printed tag actually carries.
     *
     * A bare number is useless to a phone — the camera just shows text and the
     * person still has to type it somewhere. A link opens the item's record,
     * so any phone works as a scanner, with no app and no hardware to buy.
     *
     * A USB scanner types this whole link into a scan box instead of the bare
     * number, which is why readCode() below has to take it apart again.
     */
    public static function url(string $number): string
    {
        return route('property.scan', ['property_no' => $number]);
    }

    /**
     * Turn whatever landed in a scan box back into a property number.
     *
     * Accepts a link from one of our own tags, or a number read off the
     * sticker by hand. Anything that is not one of our links passes through
     * untouched, so typing still works exactly as before.
     */
    public static function readCode(string $input): string
    {
        $input = trim($input);

        if ($input === '' || ! str_contains($input, 'property_no=')) {
            return $input;
        }

        $query = parse_url($input, PHP_URL_QUERY);

        if (! $query) {
            return $input;
        }

        parse_str($query, $params);

        $number = trim($params['property_no'] ?? '');

        return $number !== '' ? $number : $input;
    }
}