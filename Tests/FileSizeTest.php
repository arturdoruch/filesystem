<?php

namespace ArturDoruch\Filesystem\Tests;

use ArturDoruch\Filesystem\FileSize;
use PHPUnit\Framework\TestCase;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class FileSizeTest extends TestCase
{
    public function testFormat()
    {
        $fileSize = new FileSize(4.7, 'GB');

        self::assertSame(4700000000, $fileSize->getValue());
        self::assertEquals('4.70 GB', $fileSize->autoFormat(false));
        self::assertEquals('4.38 GiB', $fileSize->autoFormat(true));

        self::assertEquals('4.3772161006927 GiB', $fileSize->format('GiB'));
        self::assertEquals('4.7 GB', $fileSize->format('GB'));
        self::assertEquals('4.700', $fileSize->format('GB', 3, false));
    }


    public function testByteSize()
    {
        $fileSize = new FileSize(1023);
        self::assertEquals('1.023 KB' , $fileSize->autoFormat(false, null));
        self::assertEquals('1023 B' , $fileSize->autoFormat(true));

        $fileSize = new FileSize(1024);
        self::assertEquals('1.00 KiB' , $fileSize->autoFormat(true));

        $fileSize = new FileSize(1000.5);
        self::assertSame(1000.5 , $fileSize->getValue());
        self::assertEquals('1.0005 KB', $fileSize->format('KB', null));
    }


    public function testWithLargeSize()
    {
        $fileSize = new FileSize(123456789100200300400);
        self::assertIsFloat($fileSize->getValue());
        self::assertIsFloat($fileSize->format('MB', null, false));
        self::assertEquals('123.46 EB', $fileSize->autoFormat(false));
        self::assertEquals('107.08 EiB', $fileSize->autoFormat(true));
    }


    public function testAdd()
    {
        $fileSize = new FileSize(1, 'MB');
        $fileSize->add(new FileSize(45.5, 'KB'));

        self::assertEquals('1045.5 KB', $fileSize->format('KB'));
        self::assertSame(1045500, $fileSize->getValue());

        $fileSize = new FileSize(9223372036854775807);
        $fileSize->add(new FileSize(1));
        self::assertIsFloat($fileSize->getValue());
        self::assertSame(9223372036854775808, $fileSize->getValue());
    }


    public function testSubtract()
    {
        $fileSize = new FileSize(1, 'MB');
        $fileSize->subtract(new FileSize(100, 'KB'));

        self::assertEquals('900 KB', $fileSize->format('KB'));
        self::assertSame(900000, $fileSize->getValue());
    }


    public function testToString()
    {
        self::assertEquals('123 B', (string) new FileSize(123));
        $fileSize = new FileSize(1230.200, 'KB');
        self::assertEquals('1230.2 KB', (string) $fileSize);
        $fileSize->add(new FileSize(50.400, 'KB'));
        self::assertEquals('1280.6 KB', (string) $fileSize);
    }


    public function testCreate()
    {
        $fileSize = FileSize::create('20 B');
        self::assertSame(20, $fileSize->getValue());

        $fileSize = FileSize::create('30 kB');
        self::assertSame(30000, $fileSize->getValue());
        self::assertSame(30, $fileSize->format('KB', null, false));

        $fileSize = FileSize::create('10.57 MB');
        self::assertSame(10570000, $fileSize->getValue());
        self::assertSame(10.57, $fileSize->format('MB', null, false));
    }


    public function getInvalidSizes()
    {
        return [
            ['40'],
            [' 60 MB'],
            ['80 aB'],
            ['MB'],
        ];
    }

    /**
     * @dataProvider getInvalidSizes
     * @expectedException \InvalidArgumentException
     *
     * @param string $size
     */
    public function testCreateWithInvalidSize($size)
    {
        FileSize::create($size);
    }
}
