<?php

declare(strict_types=1);

namespace ArturDoruch\Filesystem;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class FileSize
{
    private static $binaryUnitExponentMap = [
        'KiB' => 1,
        'MiB' => 2,
        'GiB' => 3,
        'TiB' => 4,
        'PiB' => 5,
        'EiB' => 6,
    ];

    private static $decimalUnitExponentMap = [
        'KB' => 1,
        'MB' => 2,
        'GB' => 3,
        'TB' => 4,
        'PB' => 5,
        'EB' => 6,
    ];

    /**
     * @var int|float The size unit in bytes.
     */
    private $value;

    /**
     * @var string
     */
    private $unit;

    /**
     * @param int|float $value
     * @param string $unit
     *
     * @throws \InvalidArgumentException When the unit is invalid.
     */
    public function __construct($value, string $unit = 'B')
    {
        if (!is_integer($value) && !is_float($value)) {
            throw new \InvalidArgumentException(sprintf('Invalid size value. Expected an integer or float, but got "%s".', gettype($value)));
        }

        $value = self::getUnitMultiplier($unit) * $value;

        if ($value == $val = (int) $value) {
            $value = $val;
        }

        $this->value = $value;
        $this->unit = $unit;
    }

    /**
     * Gets the file size formatted to the initial unit.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->format($this->unit);
    }

    /**
     * Creates from a string.
     *
     * @param string $size The size value and unit.
     *
     * @return self
     * @throws \InvalidArgumentException When the size is invalid or contains invalid unit.
     */
    public static function create(string $size): self
    {
        if (!preg_match('/^(\d+(?:\.\d+)?) ([a-z]{1,3})$/i', $size, $parts)) {
            throw new \InvalidArgumentException(sprintf('Invalid size "%s".', $size));
        }

        if ($parts[1] != $value = (int) $parts[1]) {
            $value = (float) $parts[1];
        }

        return new self($value, str_replace('kB', 'KB', $parts[2]));
    }

    /**
     * @return int|float The size in bytes.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Formats the file size to the specified unit.
     *
     * @param string $unit
     * @param int|null $precision Decimal numbers precision. If null, no precision will be used.
     * @param bool $addUnit Whether to add unit to the returned value.
     *
     * @return string|float|int
     * @throws \InvalidArgumentException When the unit is invalid.
     */
    public function format(string $unit, ?int $precision = null, bool $addUnit = true)
    {
        $value = self::doFormat($this->value / self::getUnitMultiplier($unit), $precision);

        return $addUnit ? $value . ' ' . $unit : $value;
    }

    /**
     * Formats the file size to the unit that best fits.
     *
     * @param bool $binary Whether to format to the binary unit. If false formats to the decimal.
     * @param int|null $precision Decimal numbers precision. If null, no precision will be used.
     *
     * @return string
     */
    public function autoFormat(bool $binary, ?int $precision = 2): string
    {
        $unitExponentMap = self::getUnitExponentMap($binary, $base);

        if ($this->value < $base) {
            return $this->value . ' B';
        }

        foreach ($unitExponentMap as $unit => $exponent) {
            $unitMultiplier = pow($base, $exponent);

            if ($this->value >= $unitMultiplier && $this->value < pow($base, $exponent + 1)) {
                break;
            }
        }

        return self::doFormat($this->value / $unitMultiplier, $precision) . ' ' . $unit;
    }


    public function add(FileSize $size): self
    {
        $this->value += $size->getValue();

        return $this;
    }


    public function subtract(FileSize $size): self
    {
        $this->value -= $size->getValue();

        return $this;
    }


    private static function doFormat($value, $precision)
    {
        if ($precision === null) {
            return $value;
        }

        return sprintf('%.'.abs($precision).'f', $value);
    }


    private static function getUnitMultiplier(string $unit)
    {
        if ($unit === 'B') {
            return 1;
        }

        $unitExponentMap = self::getUnitExponentMap(strpos($unit, 'i') !== false, $base);

        if (!isset($unitExponentMap[$unit])) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid size unit "%s". Allowed units are: B, %s.', $unit,
                join(', ', array_keys(self::$binaryUnitExponentMap + self::$decimalUnitExponentMap))
            ));
        }

        return pow($base, $unitExponentMap[$unit]);
    }

    /**
     * @param bool $binary Whether to get the map for the binary units.
     * @param int $base The base of the number.
     *
     * @return array
     */
    private static function getUnitExponentMap(bool $binary, &$base): array
    {
        if ($binary) {
            $base = 1024;

            return self::$binaryUnitExponentMap;
        }

        $base = 1000;

        return self::$decimalUnitExponentMap;
    }
}
