<?php

namespace Hlw\Collect\Dy\Web\Signature;

use InvalidArgumentException;
use RuntimeException;

class ABogus
{
    private const INFO_DIC = [
        's0' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=',
        's1' => 'Dkdpgh4ZKsQB80/Mfvw36XI1R25+WUAlEi7NLboqYTOPuzmFjJnryx9HVGcaStCe=',
        's2' => 'Dkdpgh4ZKsQB80/Mfvw36XI1R25-WUAlEi7NLboqYTOPuzmFjJnryx9HVGcaStCe=',
        's3' => 'ckdp1h4ZKsUB80/Mfvw36XIgR25+WQAlEi7NLboqYTOPuzmFjJnryx9HVGDaStCe',
        's4' => 'Dkdpgh2ZmsQB80/MfvV36XI1R45-WUAlEixNLwoqYTOPuzKFjJnry79HbGcaStCe',
    ];

    public static function generate(string $params, string $ua): string
    {
        return self::getAb($params, $ua);
    }

    public static function getAb(string $params, string $ua): string
    {
        $t1 = self::nowMs();
        $t2 = $t1 - 1 + self::getRandomNumber(1, 3);
        $t3 = $t1 + self::getRandomNumber(4, 15);
        $t4 = $t1 + self::getRandomNumber(100, 1000);

        $ep = self::generateEP($ua);
        $eEp = self::encSum($ep);

        $signData = self::buildSignData($params, [
            't1' => $t1,
            't2' => $t2,
            't3' => $t3,
            't4' => $t4,
        ], $ep, $eEp);

        return self::generateFinalAB($signData, $t3);
    }

    public static function encSum($input, ?string $format = null)
    {
        $sm3 = new ABogusSm3();
        return $sm3->sum($input, $format);
    }

    public static function generateEP(string $ua): string
    {
        return self::getRawAb(self::generateLmGEP($ua), self::INFO_DIC['s3']);
    }

    private static function generateLmGEP(string $ua): string
    {
        $sz256f2 = [233, 5, 1, 249, 162, 140, 57, 143, 19, 203, 254, 236, 99, 248, 93, 213, 79, 149, 216, 50, 145, 123, 240, 92, 23, 113, 130, 53, 235, 220, 201, 136, 223, 155, 190, 242, 243, 42, 52, 214, 151, 232, 97, 187, 163, 222, 30, 78, 47, 71, 49, 170, 247, 196, 25, 156, 183, 182, 217, 180, 147, 124, 208, 69, 215, 200, 161, 154, 91, 60, 133, 224, 119, 164, 221, 45, 98, 40, 186, 120, 51, 167, 38, 90, 194, 212, 129, 56, 87, 195, 144, 44, 75, 84, 81, 13, 197, 245, 36, 250, 115, 100, 105, 252, 206, 103, 112, 202, 114, 138, 192, 21, 116, 173, 181, 29, 82, 125, 141, 16, 211, 131, 225, 118, 31, 101, 77, 146, 135, 150, 62, 66, 67, 176, 0, 41, 46, 59, 107, 178, 43, 26, 189, 128, 8, 207, 166, 110, 3, 229, 85, 54, 63, 11, 32, 4, 234, 142, 72, 58, 33, 231, 12, 230, 102, 86, 70, 159, 226, 65, 237, 34, 244, 76, 132, 122, 111, 95, 179, 152, 175, 18, 177, 6, 126, 193, 219, 74, 134, 2, 61, 251, 191, 168, 209, 241, 137, 165, 88, 238, 160, 174, 153, 157, 199, 48, 22, 64, 246, 7, 139, 55, 27, 188, 148, 204, 127, 171, 89, 37, 172, 205, 121, 20, 28, 17, 169, 15, 227, 117, 80, 218, 198, 10, 106, 9, 39, 210, 104, 83, 109, 24, 108, 228, 184, 96, 185, 158, 14, 255, 239, 68, 94, 35, 73, 253];

        $k = 0;
        $s = '';
        $len = strlen($ua);
        for ($i = 0; $i < $len; $i++) {
            $i1 = ($i + 1) % 256;
            $a = $sz256f2[$i1];
            $k = ($k + $a) % 256;
            $c = $sz256f2[$k];
            $sz256f2[$i1] = $c;
            $sz256f2[$k] = $a;
            $s .= chr((ord($ua[$i]) ^ $sz256f2[($a + $c) % 256]) & 255);
        }

        return $s;
    }

    private static function getStrChrList(string $value): array
    {
        $result = [];
        $len = strlen($value);
        for ($i = 0; $i < $len; $i++) {
            $result[] = ord($value[$i]);
        }
        return $result;
    }

    private static function generateSzencHead8p1(): array
    {
        $z = random_int(0, 65534);
        $a = $z & 255;
        $b = ($z >> 8) & 255;

        return [
            ($a & 170) | 1,
            ($a & 85) | 0,
            ($b & 170) | 0,
            ($b & 85) | 0,
        ];
    }

    private static function generateSzencHead8p2(): array
    {
        $a = random_int(1, 240);
        $b = random_int(0, 254) & 77;
        foreach ([1, 4, 5, 7] as $bit) {
            $b |= (1 << $bit);
        }

        return [
            ($a & 170) | 1,
            ($a & 85) | 0,
            ($b & 170) | 0,
            ($b & 85) | 0,
        ];
    }

    private static function getSzencTail(array $sz96): array
    {
        $keySz6 = [145, 110, 66, 189, 44, 211];
        $result = [];
        for ($i = 0; $i < 94; $i += 3) {
            $b = $sz96[$i] ?? 0;
            $c = $sz96[$i + 1] ?? 0;
            $d = $sz96[$i + 2] ?? 0;
            $e = random_int(0, 999) & 255;
            $result[] = ($e & $keySz6[0]) | ($b & $keySz6[1]);
            $result[] = ($e & $keySz6[2]) | ($c & $keySz6[3]);
            $result[] = ($e & $keySz6[4]) | ($d & $keySz6[5]);
            $result[] = (($b & $keySz6[0]) | ($c & $keySz6[2])) | ($d & $keySz6[4]);
        }
        return $result;
    }

    private static function generateLmGAbHead4(): string
    {
        $a = random_int(0, 65534) & 255;
        $b = random_int(0, 39);

        return chr(($a & 170) | 1)
            . chr(($a & 85) | 2)
            . chr(($b & 170) | 80)
            . chr(($b & 85) | 2);
    }

    private static function getListStr(array $list): string
    {
        $s = '';
        foreach ($list as $value) {
            $s .= chr($value & 255);
        }
        return $s;
    }

    private static function getLmGAb(string $lm): string
    {
        $fixedSz256 = [194, 249, 255, 165, 114, 67, 251, 187, 174, 231, 164, 237, 124, 235, 68, 83, 206, 79, 142, 167, 30, 77, 0, 93, 118, 29, 32, 161, 2, 171, 243, 179, 42, 170, 223, 119, 98, 222, 219, 57, 245, 135, 197, 13, 186, 202, 88, 184, 214, 12, 76, 185, 116, 74, 54, 53, 104, 208, 158, 163, 82, 173, 253, 240, 172, 63, 191, 207, 25, 15, 201, 203, 215, 236, 183, 233, 145, 127, 72, 6, 16, 10, 228, 35, 232, 159, 66, 168, 108, 71, 217, 75, 33, 155, 112, 128, 36, 24, 138, 50, 211, 23, 107, 14, 247, 137, 175, 242, 234, 157, 199, 49, 139, 85, 81, 17, 180, 86, 120, 78, 51, 205, 169, 148, 181, 3, 94, 106, 252, 220, 150, 47, 151, 84, 212, 18, 149, 182, 100, 123, 121, 156, 154, 152, 126, 204, 60, 133, 132, 248, 7, 91, 58, 59, 20, 97, 113, 117, 131, 46, 250, 224, 21, 73, 146, 31, 193, 69, 140, 125, 9, 39, 89, 5, 65, 141, 218, 80, 1, 70, 64, 166, 87, 189, 55, 147, 22, 26, 143, 61, 144, 99, 92, 44, 129, 130, 227, 103, 90, 192, 198, 244, 136, 101, 246, 153, 56, 38, 4, 178, 221, 162, 134, 37, 111, 28, 216, 96, 102, 210, 254, 196, 195, 230, 241, 62, 11, 122, 52, 40, 41, 229, 226, 225, 48, 45, 160, 105, 8, 115, 34, 43, 209, 95, 239, 190, 188, 109, 27, 19, 176, 213, 200, 238, 177, 110];

        $z = 0;
        $st = '';
        $len = strlen($lm);
        for ($i = 0; $i < $len; $i++) {
            $a = ($i + 1) % 256;
            $c = $fixedSz256[$a];
            $z = ($z + $c) % 256;
            $e = $fixedSz256[$z];
            $fixedSz256[$a] = $e;
            $fixedSz256[$z] = $c;
            $g = ($e + $c) % 256;
            $h = ord($lm[$i]);
            $j = $fixedSz256[$g];
            $st .= chr(($h ^ $j) & 255);
        }

        return $st;
    }

    private static function getRawAb(string $input, string $keyStr = self::INFO_DIC['s4']): string
    {
        $s = '';
        $len = strlen($input);
        for ($i = 0; $i < $len; $i += 3) {
            $cl = 16;
            $tcz = 0;
            $sof = 16515072;
            $bw = 0;

            for ($j = $i; $j < $i + 3; $j++) {
                if ($j < $len) {
                    $tlcy = ord($input[$j]) & 255;
                    $tcz |= ($tlcy << $cl);
                    $cl -= 8;
                } else {
                    $bw++;
                }
            }

            for ($h = 18; $h >= 6 * $bw; $h -= 6) {
                $tsz = $tcz & $sof;
                $s .= $keyStr[$tsz >> $h];
                $sof = intdiv($sof, 64);
            }

            $s .= str_repeat('=', $bw);
        }

        return $s;
    }

    private static function getRandomNumber(int $min, int $max): int
    {
        return random_int($min, $max);
    }

    private static function buildSignData(string $params, array $timestamps, string $ep, array $eEp): array
    {
        $t1 = $timestamps['t1'];
        $t2 = $timestamps['t2'];
        $t3 = $timestamps['t3'];
        $t4 = $timestamps['t4'];

        $s = [];
        array_push($s, 'env_fx_list', 'dpf_ua_dic', 1, 0, 8, 'dpf', '', 'ua', 6241, 6383, '1.0.1.19-fix.01', 'ink', 3, '0X21_dic');

        $eedp = self::encSum(self::encSum($params . 'dhzx'));
        array_push($s, $t3, 'reg_dic', 1, 0, $eedp, 'eedh', $ep, $eEp, $t2, [3, 82], 41, [1, 0, 1, 0, 1]);

        $s1 = (int)(($t4 - 1721836800000) / 1000 / 60 / 60 / 24 / 14);
        $szencO95Tail41 = [49, 52, 52, 49, 124, 56, 51, 56, 124, 49, 52, 52, 49, 124, 57, 49, 51, 124, 49, 52, 52, 49, 124, 57, 49, 51, 124, 49, 52, 52, 49, 124, 57, 54, 49, 124, 87, 105, 110, 51, 50];

        array_push($s, $s1, 6, ($t3 - $t1 + 3) & 255, $t3 & 255, ($t3 >> 8) & 255, ($t3 >> 16) & 255, ($t3 >> 24) & 255, (int)floor($t3 / 256 / 256 / 256 / 256) & 255);

        $s2 = (int)floor($t3 / 256 / 256 / 256 / 256 / 256) & 255;
        array_push(
            $s,
            $s2,
            ($s2 % 256) & 255,
            (int)floor($s2 / 256) & 255,
            [211, 2, 5, 1, 129],
            129,
            0,
            211,
            2,
            5,
            1,
            0,
            0,
            0,
            0,
            $eedp[9],
            $eedp[18],
            3,
            $eedp[3],
            82,
            177,
            4,
            44,
            $eEp[11],
            $eEp[21],
            5,
            $eEp[5],
            $t2 & 255,
            ($t2 >> 8) & 255,
            ($t2 >> 16) & 255,
            ($t2 >> 24) & 255,
            (int)floor($t2 / 256 / 256 / 256 / 256) & 255,
            (int)floor($t2 / 256 / 256 / 256 / 256 / 256) & 255,
            3,
            97,
            24,
            0,
            0,
            239,
            24,
            0,
            0,
            'screec_dic',
            'screen_str',
            $szencO95Tail41,
            41,
            41,
            0
        );

        return ['s' => $s, 'szenc_o95_tail41' => $szencO95Tail41];
    }

    private static function generateFinalAB(array $signData, int $t3): string
    {
        $s = $signData['s'];
        $szencO95Tail41 = $signData['szenc_o95_tail41'];

        $s3 = (($t3 + 3) & 255) . ',';
        $s4 = self::getStrChrList($s3);

        array_push($s, $s3, $s4, count($s4), count($s4) & 255, (count($s4) >> 8) & 255);

        $szencHead8 = array_merge(self::generateSzencHead8p1(), self::generateSzencHead8p2());
        $s5 = [];
        $s6 = [24, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 51, 52, 53, 55, 56, 57, 59, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 79, 80, 84, 85];

        foreach ($s6 as $index) {
            $s5[] = $s[$index];
        }

        $s[] = $szencHead8;
        $s7 = array_merge($szencHead8, $s5);
        $s8 = $s7[0];
        for ($i = 1, $len = count($s7); $i < $len; $i++) {
            $s8 ^= $s7[$i];
        }
        $s[] = $s8;

        $encSI = [34, 44, 56, 61, 73, 29, 70, 45, 35, 49, 38, 66, 51, 68, 28, 48, 64, 47, 30, 71, 26, 55, 31, 69, 59, 40, 62, 63, 27, 72, 41, 74, 57, 52, 42, 39, 33, 67, 53, 43, 65, 46, 36, 24, 60, 32, 79, 80, 84, 85];
        $szencO95Head50 = [];
        foreach ($encSI as $index) {
            $szencO95Head50[] = $s[$index];
        }

        $szencO95 = array_merge($szencO95Head50, $szencO95Tail41, $s4, [$s8]);
        $szencTail = self::getSzencTail($szencO95);
        $szenc = array_merge($szencHead8, $szencTail);

        $lmGetAb = self::generateLmGAbHead4() . self::getLmGAb(self::getListStr($szenc));
        return self::getRawAb($lmGetAb);
    }

    private static function nowMs(): int
    {
        return (int)floor(microtime(true) * 1000);
    }
}

class ABogusSm3
{
    private array $reg = [];
    private array $chunk = [];
    private int $size = 0;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->reg = [
            1937774191,
            1226093241,
            388252375,
            3666478592,
            2842636476,
            372324522,
            3817729613,
            2969243214,
        ];
        $this->chunk = [];
        $this->size = 0;
    }

    public function write($input): void
    {
        $bytes = is_string($input) ? $this->stringBytes($input) : array_values($input);
        $this->size += count($bytes);
        $e = 64 - count($this->chunk);

        if (count($bytes) < $e) {
            $this->chunk = array_merge($this->chunk, $bytes);
            return;
        }

        $this->chunk = array_merge($this->chunk, array_slice($bytes, 0, $e));
        while (count($this->chunk) >= 64) {
            $this->compress($this->chunk);
            $this->chunk = $e < count($bytes) ? array_slice($bytes, $e, min(64, count($bytes) - $e)) : [];
            $e += 64;
        }
    }

    public function sum($input = null, ?string $format = null)
    {
        if ($input !== null) {
            $this->reset();
            $this->write($input);
        }

        $this->fill();
        for ($i = 0, $len = count($this->chunk); $i < $len; $i += 64) {
            $this->compress(array_slice($this->chunk, $i, 64));
        }

        if ($format === 'hex') {
            $result = '';
            for ($i = 0; $i < 8; $i++) {
                $result .= str_pad(dechex($this->reg[$i]), 8, '0', STR_PAD_LEFT);
            }
            $this->reset();
            return $result;
        }

        $result = array_fill(0, 32, 0);
        for ($i = 0; $i < 8; $i++) {
            $s = $this->reg[$i];
            $result[4 * $i + 3] = $s & 255;
            $s >>= 8;
            $result[4 * $i + 2] = $s & 255;
            $s >>= 8;
            $result[4 * $i + 1] = $s & 255;
            $s >>= 8;
            $result[4 * $i] = $s & 255;
        }

        $this->reset();
        return $result;
    }

    private function compress(array $chunk): void
    {
        if (count($chunk) < 64) {
            throw new RuntimeException('compress error: not enough data');
        }

        $r = $this->expand($chunk);
        $e = $this->reg;

        for ($n = 0; $n < 64; $n++) {
            $o = self::u32(self::rotl($e[0], 12) + $e[4] + self::rotl($this->tj($n), $n));
            $o = self::rotl($o, 7);
            $i = self::u32($o ^ self::rotl($e[0], 12));
            $u = self::u32($this->ff($n, $e[0], $e[1], $e[2]) + $e[3] + $i + $r[$n + 68]);
            $s = self::u32($this->gg($n, $e[4], $e[5], $e[6]) + $e[7] + $o + $r[$n]);

            $e[3] = $e[2];
            $e[2] = self::rotl($e[1], 9);
            $e[1] = $e[0];
            $e[0] = $u;
            $e[7] = $e[6];
            $e[6] = self::rotl($e[5], 19);
            $e[5] = $e[4];
            $e[4] = self::u32($s ^ self::rotl($s, 9) ^ self::rotl($s, 17));
        }

        for ($c = 0; $c < 8; $c++) {
            $this->reg[$c] = self::u32($this->reg[$c] ^ $e[$c]);
        }
    }

    private function expand(array $chunk): array
    {
        $r = array_fill(0, 132, 0);
        for ($e = 0; $e < 16; $e++) {
            $r[$e] = self::u32(($chunk[4 * $e] << 24) | ($chunk[4 * $e + 1] << 16) | ($chunk[4 * $e + 2] << 8) | $chunk[4 * $e + 3]);
        }

        for ($n = 16; $n < 68; $n++) {
            $o = self::u32($r[$n - 16] ^ $r[$n - 9] ^ self::rotl($r[$n - 3], 15));
            $o = self::u32($o ^ self::rotl($o, 15) ^ self::rotl($o, 23));
            $r[$n] = self::u32($o ^ self::rotl($r[$n - 13], 7) ^ $r[$n - 6]);
        }

        for ($n = 0; $n < 64; $n++) {
            $r[$n + 68] = self::u32($r[$n] ^ $r[$n + 4]);
        }

        return $r;
    }

    private function fill(): void
    {
        $t = 8 * $this->size;
        $this->chunk[] = 128;
        $r = count($this->chunk) % 64;
        if (64 - $r < 8) {
            $r -= 64;
        }

        for (; $r < 56; $r++) {
            $this->chunk[] = 0;
        }

        $high = (int)floor($t / 4294967296);
        for ($e = 0; $e < 4; $e++) {
            $this->chunk[] = ($high >> (8 * (3 - $e))) & 255;
        }
        for ($e = 0; $e < 4; $e++) {
            $this->chunk[] = ($t >> (8 * (3 - $e))) & 255;
        }
    }

    private function stringBytes(string $input): array
    {
        $bytes = [];
        $len = strlen($input);
        for ($i = 0; $i < $len; $i++) {
            $bytes[] = ord($input[$i]);
        }
        return $bytes;
    }

    private function tj(int $j): int
    {
        if ($j >= 0 && $j < 16) {
            return 2043430169;
        }
        if ($j >= 16 && $j < 64) {
            return 2055708042;
        }
        throw new InvalidArgumentException('invalid j for constant Tj');
    }

    private function ff(int $j, int $x, int $y, int $z): int
    {
        if ($j >= 0 && $j < 16) {
            return self::u32($x ^ $y ^ $z);
        }
        if ($j >= 16 && $j < 64) {
            return self::u32(($x & $y) | ($x & $z) | ($y & $z));
        }
        throw new InvalidArgumentException('invalid j for bool function FF');
    }

    private function gg(int $j, int $x, int $y, int $z): int
    {
        if ($j >= 0 && $j < 16) {
            return self::u32($x ^ $y ^ $z);
        }
        if ($j >= 16 && $j < 64) {
            return self::u32(($x & $y) | ((~$x) & $z));
        }
        throw new InvalidArgumentException('invalid j for bool function GG');
    }

    private static function rotl(int $value, int $bits): int
    {
        $bits %= 32;
        return self::u32((self::u32($value) << $bits) | (self::u32($value) >> (32 - $bits)));
    }

    private static function u32(int $value): int
    {
        return $value & 0xffffffff;
    }
}
