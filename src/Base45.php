<?php
declare(strict_types=1);

namespace Causal\DGC;

/**
 * Based on https://github.com/baumann-at/base45-php
 *
 * Original author: Chris Baumann - c.baumann@baumann.at
 * Specification: https://datatracker.ietf.org/doc/draft-faltstrom-base45/
 */
class Base45
{
    private const CHARSET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ $%*+-./:';

    public static function decode(string $input): string
    {
        $buffer = self::b45str2buffer($input);
        $res = '';
        for ($i = 0, $iMax = count($buffer); $i < $iMax; $i += 3) {
            if ($iMax - $i >= 3) {
                $x = $buffer[$i] + $buffer[$i + 1] * 45 + $buffer[$i + 2] * 45 * 45;
                [$a, $b] = self::divmod($x, 256);
                $res .= chr($a) . chr($b);
            } else {
                $x = $buffer[$i] + $buffer[$i + 1] * 45;
                $res .= chr($x);
            }
        }
        return ($res);
    }

    private static function divmod(int $x, int $y): array
    {
        $resX = (int)floor($x / $y);
        $resY = $x % $y;
        return [$resX, $resY];
    }

    private static function b45str2buffer(string $s): array
    {
        $res = [];
        for ($i = 0; $i < strlen($s); $i++) {
            $p = strpos(self::CHARSET, $s[$i]);
            if ($p === false) {
                throw new \Exception('Invalid base45 value');
            } else {
                $res[] = $p;
            }
        }
        return $res;
    }

}
