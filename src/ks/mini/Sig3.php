<?php

namespace Hlw\Collect\Ks\Mini;

class Sig3
{
    private const IV1 = [
        1956091773,
        -1549544051,
        -366913359,
        928895576,
        326699590,
        -1411767009,
        2045457372,
        -1998927491,
    ];

    private const IV2 = [
        1044012663,
        1223011054,
        968960882,
        1401689337,
        1308580599,
        1329378352,
        1694219527,
        1062964844,
    ];

    private const K = [
        1116352408, 1899447441, 3049323471, 3921009573, 961987163, 1508970993, 2453635748, 2870763221,
        3624381080, 310598401, 607225278, 1426881987, 1925078388, 2162078206, 2614888103, 3248222580,
        3835390401, 4022224774, 264347078, 604807628, 770255983, 1249150122, 1555081692, 1996064986,
        2554220882, 2821834349, 2952996808, 3210313671, 3336571891, 3584528711, 113926993, 338241895,
        666307205, 773529912, 1294757372, 1396182291, 1695183700, 1986661051, 2177026350, 2456956037,
        2730485921, 2820302411, 3259730800, 3345764771, 3516065817, 3600352804, 4094571909, 275423344,
        430227734, 506948616, 659060556, 883997877, 958139571, 1322822218, 1537002063, 1747873779,
        1955562222, 2024104815, 2227730452, 2361852424, 2428436474, 2756734187, 3204031479, 3329325298,
    ];

    private int $startupRandom;
    private int $count;
    private ?int $now;

    public function __construct(?int $startupRandom = null, int $count = 100, ?int $now = null)
    {
        $this->startupRandom = $startupRandom ?? time();
        $this->count = $count;
        $this->now = $now;
    }

    public function generate(string $arg): string
    {
        $currentCount = $this->count;
        $this->count++;

        $digest1 = self::customSha256($arg, self::IV1);
        $digest1Bytes = self::wordsToBytes($digest1);
        $digest2 = self::customSha256Bytes($digest1Bytes, self::IV2);

        $body = array_merge(
            [0x54, 0x45, 0x02, 0x30],
            self::int32BytesLe($this->startupRandom),
            self::int32BytesLe($currentCount),
            self::int32BytesBe($digest2[0]),
            self::int32BytesLe($this->now ?? time()),
            [0x01, 0x00, 0x01, 0x00, 0x00, 0x00, 0x00]
        );

        $checksum = self::hexByteChecksum(self::bytesToHex($body));
        $body[] = $checksum;

        return self::encodeOutput($body);
    }

    public function getCount(): int
    {
        return $this->count;
    }

    private static function customSha256(string $input, array $iv): array
    {
        $bytes = [];
        $length = strlen($input);
        for ($i = 0; $i < $length; $i++) {
            $bytes[] = ord($input[$i]);
        }
        return self::customSha256Bytes($bytes, $iv, 64);
    }

    private static function customSha256Bytes(array $bytes, array $iv, int $lengthOffset = 64): array
    {
        return self::compress(self::makeBlocks($bytes, $lengthOffset), $iv);
    }

    private static function makeBlocks(array $bytes, int $lengthOffset): array
    {
        $bitLength = (count($bytes) + $lengthOffset) * 8;
        $bytes[] = 0x80;

        while (count($bytes) % 64 !== 56) {
            $bytes[] = 0;
        }

        $high = (int)floor($bitLength / 4294967296);
        $low = $bitLength & 0xffffffff;
        foreach ([$high, $low] as $value) {
            $bytes[] = ($value >> 24) & 255;
            $bytes[] = ($value >> 16) & 255;
            $bytes[] = ($value >> 8) & 255;
            $bytes[] = $value & 255;
        }

        $blocks = [];
        for ($i = 0, $length = count($bytes); $i < $length; $i += 64) {
            $block = [];
            for ($j = 0; $j < 16; $j++) {
                $k = $i + $j * 4;
                $block[] = self::u32(($bytes[$k] << 24) | ($bytes[$k + 1] << 16) | ($bytes[$k + 2] << 8) | $bytes[$k + 3]);
            }
            $blocks[] = $block;
        }

        return $blocks;
    }

    private static function compress(array $blocks, array $iv): array
    {
        $h = array_map([self::class, 'u32'], $iv);

        foreach ($blocks as $block) {
            $w = array_fill(0, 64, 0);
            for ($i = 0; $i < 16; $i++) {
                $w[$i] = self::u32($block[$i]);
            }
            for ($i = 16; $i < 64; $i++) {
                $s0 = self::u32(self::rotr($w[$i - 15], 7) ^ self::rotr($w[$i - 15], 18) ^ ($w[$i - 15] >> 3));
                $s1 = self::u32(self::rotr($w[$i - 2], 17) ^ self::rotr($w[$i - 2], 19) ^ ($w[$i - 2] >> 10));
                $w[$i] = self::u32($w[$i - 16] + $s0 + $w[$i - 7] + $s1);
            }

            [$a, $b, $c, $d, $e, $f, $g, $hh] = $h;
            for ($i = 0; $i < 64; $i++) {
                $s1 = self::u32(self::rotr($e, 6) ^ self::rotr($e, 11) ^ self::rotr($e, 25));
                $ch = self::u32(($e & $f) ^ ((~$e) & $g));
                $temp1 = self::u32($hh + $s1 + $ch + self::K[$i] + $w[$i]);
                $s0 = self::u32(self::rotr($a, 2) ^ self::rotr($a, 13) ^ self::rotr($a, 22));
                $maj = self::u32(($a & $b) ^ ($a & $c) ^ ($b & $c));
                $temp2 = self::u32($s0 + $maj);

                $hh = $g;
                $g = $f;
                $f = $e;
                $e = self::u32($d + $temp1);
                $d = $c;
                $c = $b;
                $b = $a;
                $a = self::u32($temp1 + $temp2);
            }

            $h = [
                self::u32($h[0] + $a),
                self::u32($h[1] + $b),
                self::u32($h[2] + $c),
                self::u32($h[3] + $d),
                self::u32($h[4] + $e),
                self::u32($h[5] + $f),
                self::u32($h[6] + $g),
                self::u32($h[7] + $hh),
            ];
        }

        return $h;
    }

    private static function wordsToBytes(array $words): array
    {
        $bytes = [];
        foreach ($words as $word) {
            $word = self::u32($word);
            $bytes[] = ($word >> 24) & 255;
            $bytes[] = ($word >> 16) & 255;
            $bytes[] = ($word >> 8) & 255;
            $bytes[] = $word & 255;
        }
        return $bytes;
    }

    private static function int32BytesLe(int $value): array
    {
        $value = self::u32($value);
        return [
            $value & 255,
            ($value >> 8) & 255,
            ($value >> 16) & 255,
            ($value >> 24) & 255,
        ];
    }

    private static function int32BytesBe(int $value): array
    {
        $value = self::u32($value);
        return [
            ($value >> 24) & 255,
            ($value >> 16) & 255,
            ($value >> 8) & 255,
            $value & 255,
        ];
    }

    private static function hexByteChecksum(string $hex): int
    {
        $sum = 0;
        for ($i = 0, $length = strlen($hex); $i < $length; $i += 2) {
            $sum += hexdec(substr($hex, $i, 2)) & 255;
        }
        if ($sum > 255) {
            $sum = ~$sum + 1;
        }
        return $sum & 255;
    }

    private static function encodeOutput(array $bytes): string
    {
        $checksum = $bytes[count($bytes) - 1] & 255;
        $encoded = [];
        foreach ($bytes as $index => $byte) {
            $encoded[] = $index === count($bytes) - 1 ? ($byte & 255) : (($byte ^ ($checksum ^ $index)) & 255);
        }
        return self::bytesToHex($encoded);
    }

    private static function bytesToHex(array $bytes): string
    {
        $hex = '';
        foreach ($bytes as $byte) {
            $hex .= str_pad(dechex($byte & 255), 2, '0', STR_PAD_LEFT);
        }
        return $hex;
    }

    private static function rotr(int $value, int $bits): int
    {
        $value = self::u32($value);
        return self::u32(($value >> $bits) | ($value << (32 - $bits)));
    }

    private static function u32(int $value): int
    {
        return $value & 0xffffffff;
    }
}

