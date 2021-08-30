<?php
declare(strict_types=1);

namespace Causal\DGC;

class Decoder
{
    public function decodeFromQR(string $input): array
    {
        if (strpos($input, 'HC1:') !== 0) {
            throw new \Exception('Unsupported QR data');
        }

        $b45Data = substr($input, 4);
        $zlibData = Base45::decode($b45Data);
        $cborData = zlib_decode($zlibData);

        $cborDecoder = new \Firehed\CBOR\Decoder();
        $rawData = json_decode($cborDecoder->decode($cborData), true);

        return $rawData;
    }
}
