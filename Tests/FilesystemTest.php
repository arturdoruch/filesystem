<?php

namespace ArturDoruch\Filesystem\Tests;

use ArturDoruch\Filesystem\Directory;
use ArturDoruch\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class FilesystemTest extends TestCase
{
    private static $filesDir;

    public static function setUpBeforeClass()
    {
        self::$filesDir = __DIR__ . '/Resources/dir';
        Filesystem::remove(self::$filesDir);
    }


    public static function tearDownAfterClass()
    {
        Filesystem::remove(self::$filesDir);
    }


    public function testWriteToNotExistingDirectoryAndFile()
    {
        $filename = self::$filesDir . '/not_exists/name.txt';

        Filesystem::write($filename, $text = 'Text');

        self::assertTrue(file_exists($filename));
        self::assertEquals($text, Filesystem::read($filename));
    }


    public function testWrite()
    {
        $filename = self::$filesDir . '/write_contents.txt';

        Filesystem::write($filename, $text = "line1\nline2\n");

        $content = Filesystem::read($filename);
        self::assertEquals($text, $content);
    }


    public function testAppend()
    {
        $filename = self::$filesDir . '/append_contents.txt';

        Filesystem::append($filename, "line1\n");
        Filesystem::write($filename, "line2\nline3\n");
        Filesystem::append($filename, "line4\n");

        $contents = Filesystem::read($filename, true);
        self::assertCount(3, $contents);
        self::assertEquals('line2', $contents[0]);
    }


    public function testCreateDirectory()
    {
        $dir = self::$filesDir . '/create_dir/level1/level2/level3';
        Filesystem::createDirectory($dir);

        self::assertTrue(file_exists($dir));
    }


    public function testRead()
    {
        $filename = self::$filesDir . '/file10.txt';
        Filesystem::write($filename, 'Text');
        self::assertEquals('Text', Filesystem::read($filename));
        self::assertEquals(['Text'], Filesystem::read($filename, true));
    }

    /**
     * @expectedException \ArturDoruch\Filesystem\Exception\IOException
     * @expectedExceptionMessageRegExp /File does not exist/
     */
    public function testReadNotExistFile()
    {
        Filesystem::read(self::$filesDir . '/abc.txt');
    }

    /**
     * @expectedException \ArturDoruch\Filesystem\Exception\IOException
     * @expectedExceptionMessageRegExp /Path is not a file path/
     */
    public function testReadFromDirectory()
    {
        Filesystem::read(self::$filesDir);
    }


    public function testRemoveFileAndEmptyParentDirs()
    {
        $baseDir = self::$filesDir . '/remove_file_and_empty_parent_dirs';
        $nestedDir = $baseDir . '/1/2/3';

        // Remove directory: 1
        Filesystem::createDirectory($nestedDir);
        touch($filename = $nestedDir . '/file.txt');

        Filesystem::remove($filename, 1);
        self::assertTrue(file_exists($baseDir . '/1/2'));
        self::assertFalse(file_exists($nestedDir . '/1/2/3'));

        // Remove directories: 3, 2, 1
        Filesystem::createDirectory($nestedDir);
        touch($filename = $nestedDir . '/file.txt');

        Filesystem::remove($filename, 3);
        self::assertTrue(file_exists($baseDir));
        self::assertFalse(file_exists($baseDir . '/1'));
    }

    
    public function testRemoveFilesAndDirectories()
    {
        // Create dirs.
        $mainDir = self::$filesDir . '/remove_test';
        Filesystem::createDirectory($mainDir);
        Filesystem::createDirectory($mainDir . '/level2');
        Filesystem::createDirectory($mainDir . '/level2-2');
        Filesystem::createDirectory($mainDir . '/level2/level3');
        touch($mainDir . '/file1.txt');
        touch($mainDir . '/file2.txt');
        touch($mainDir . '/level2/level3/file3.txt');

        Filesystem::remove([
            $mainDir . '/file1.txt',
            $mainDir . '/level2',
        ]);

        $files = iterator_to_array(new \FilesystemIterator($mainDir, \FilesystemIterator::SKIP_DOTS));

        self::assertCount(2, $files);
        /** @var \SplFileInfo $file1 */
        $file1 = array_shift($files);
        $file2 = array_shift($files);

        self::assertTrue($file1->isFile());
        self::assertEquals('file2.txt', $file1->getFilename());
        self::assertTrue($file2->isDir());
        self::assertEquals('level2-2', $file2->getFilename());
    }


    public function testRenameDirectoryAndFile()
    {
        $oldDir = self::$filesDir . '/rename_dir';
        $newDir = self::$filesDir . '/renamed';
        Filesystem::createDirectory($oldDir);
        Filesystem::rename($oldDir, $newDir);

        self::assertTrue(file_exists($newDir));

        touch($oldFilename = $newDir . '/rename_file.txt');
        $newFilename = $newDir . '/renamed.txt';

        Filesystem::rename($oldFilename, $newFilename);
        self::assertTrue(file_exists($newFilename));

        Filesystem::remove($newDir);
    }


    public function testScanDirectory()
    {
        $dir = self::$filesDir . '/scan';
        Filesystem::createDirectory($dir);
        Filesystem::createDirectory($dir . '/level-1');
        Filesystem::createDirectory($dir . '/level-2/level3');
        Filesystem::createDirectory($dir . '/level-2-2');
        touch($dir . '/.gitignore');
        touch($dir . '/.dist');
        touch($dir . '/level-2/level3/testFile.txt');

        $directory = Filesystem::scanDirectory($dir);

        self::assertCount(2, $directory->getFiles());
        self::assertCount(3, $subDirs = $directory->getDirectories());

        self::assertCount(1, $subSubDirectory = $subDirs[1]->getDirectories());
        self::assertEquals('level3', $subSubDirectory[0]->getBaseName());
        self::assertEquals('testFile.txt', $subSubDirectory[0]->getFiles()[0]->getBaseName());
        //echo $this->renderDirStructure($directory, 0);
    }


    private function renderDirStructure(Directory $directory, int $depth)
    {
        $indent = str_repeat('    ', $depth);
        $html = $indent . 'dir: ' . $directory->getBasename() . "\n";

        foreach ($directory->getDirectories() as $dir) {
            $html .= $this->renderDirStructure($dir, ++$depth);
        }

        foreach ($directory->getFiles() as $file) {
            $html .= $indent . '    file: ' . $file->getBasename() . ', extension: ' . $file->getExtension() . ' ' . "\n";
        }

        return $html;
    }
}
