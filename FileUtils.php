<?php

namespace ArturDoruch\Filesystem;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class FileUtils
{
    /**
     * @param int $size The size to format in bytes.
     * @param string $unit The size unit. One of the: KB, MB, GB, TB, PB, KiB, MiB, GiB, TiB, PiB.
     * @param int|null $precision Decimal numbers precision. If null, no precision will be used.
     * @param bool $addUnit Whether to append unit to the formatted value.
     *
     * @return string
     */
    public static function formatSize(int $size, string $unit, int $precision = null, bool $addUnit = true): string
    {
        static $unitExponentMap = [
            'KB' => 1,
            'KiB' => 1,
            'MB' => 2,
            'MiB' => 2,
            'GB' => 3,
            'GiB' => 3,
            'TB' => 4,
            'TiB' => 4,
            'PB' => 5,
            'PiB' => 5,
        ];

        if (!isset($unitExponentMap[$unit])) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid size unit "%s". Allowed units are: "%s".', $unit, join('", "', array_keys($unitExponentMap))
            ));
        }

        $base = strpos($unit, 'i') !== false ? 1024 : 1000;

        return sprintf('%.'.$precision.'f', $size / pow($base, $unitExponentMap[$unit])) . ($addUnit ? ' ' . $unit : '');
    }
}
