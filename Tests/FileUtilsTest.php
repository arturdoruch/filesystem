<?php

namespace ArturDoruch\Filesystem\Tests;

use ArturDoruch\Filesystem\FileUtils;
use PHPUnit\Framework\TestCase;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class FileUtilsTest extends TestCase
{
    public function testFormatSize()
    {
        self::assertEquals(1.000, FileUtils::formatSize(1000, 'KB', 3, false));
        self::assertEquals(0.001, FileUtils::formatSize(1000, 'MB', 3, false));

        self::assertEquals(0.9765625000, FileUtils::formatSize(1000, 'KiB', 10, false));
        self::assertEquals(0.0009536743, FileUtils::formatSize(1000, 'MiB', 10, false));
        self::assertEquals(465.661287, FileUtils::formatSize(500000000000, 'GiB', null, false));
        self::assertEquals(500, FileUtils::formatSize(500000000000, 'GB', null, false));

        self::assertEquals(1, FileUtils::formatSize(1024, 'KiB', 0, false));
        self::assertEquals(1, FileUtils::formatSize(1048576, 'MiB', 0, false));
    }
}
