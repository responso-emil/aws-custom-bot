<?php

declare(strict_types=1);

namespace App\Utils;

final class RandomStringGenerator
{
    public static function generate(int $length = 10): string
    {
        return bin2hex(random_bytes($length));
    }
}
