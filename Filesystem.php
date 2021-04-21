<?php

namespace ArturDoruch\Filesystem;

use ArturDoruch\Filesystem\Exception\IOException;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class Filesystem
{
    /**
     * Writes contents into the file.
     * When the file directory does not exists, then is created.
     *
     * @todo Maybe rename method name to "putContents".
     *
     * @param string $filename Path to the file. If not exists, then will be created.
     * @param string|array|resource $contents
     * @param int $flags The flags for use by the "file_put_contents()" function.
     *
     * @return int
     * @throws IOException
     */
    public static function write(string $filename, $contents, int $flags = null): int
    {
        if (!is_dir($dir = dirname($filename))) {
            self::createDirectory($dir);
        }

        if (false === $result = @file_put_contents($filename, $contents, $flags)) {
            throw new IOException(sprintf('Failed to write the file "%s".', $filename), $filename);
        }

        return $result;
    }

    /**
     * Appends contents to the existing contents of the file.
     *
     * @todo Maybe rename method name to "appendContents".
     *
     * @param string $filename Path to the file. If not exists, then will be created.
     * @param string|array|resource $contents
     * param bool $addNewLine Whether to add a new line at the end of the string content.
     *
     * @return int
     */
    public static function append(string $filename, $contents): int
    {
        return self::write($filename, $contents, FILE_APPEND);
    }

    /**
     * Reads file into a string or an array.
     *
     * @todo Maybe rename method name to "getContents".
     *
     * @param string $filename Path to the file.
     * @param bool $toArray Whether to return an array with items of contents line.
     *
     * @return string|array File contents.
     * @throws IOException
     */
    public static function read(string $filename, bool $toArray = false)
    {
        $contents = $toArray === true ? @file($filename, FILE_IGNORE_NEW_LINES) : @file_get_contents($filename);

        if ($contents === false) {
            if (!file_exists($filename)) {
                $reason = 'File does not exist.';
            } elseif (!is_file($filename)) {
                $reason = 'Path is not a file path.';
            } elseif (strlen($filename) > 255 && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $reason = 'File path too long.';
            }

            throw new IOException(sprintf('Failed to read the file "%s".', $filename), $filename, $reason ?? null);
        }

        return $contents;
    }

    /**
     * Renames a file or directory.
     *
     * @param string $origin The origin filename or directory.
     * @param string $target The new filename or directory.
     *
     * @throws IOException
     */
    public static function rename(string $origin, string $target)
    {
        if (@rename($origin, $target) === false) {
            throw new IOException(sprintf('Failed to rename "%s".', $origin), $origin);
        }
    }

    /**
     * Removes files or directories recursively.
     *
     * @param string|array|\Traversable $files A filename, an array of files, or a \Traversable instance to remove.
     * @param int $removeEmptyParentDirs Total following empty parent directories of the file to remove, after removing the file.
     *
     * @throws IOException When a file or directory could not be removed.
     */
    public static function remove($files, int $removeEmptyParentDirs = 0)
    {
        if ($files instanceof \Traversable) {
            $files = iterator_to_array($files, false);
        }

        $files = array_reverse((array) $files);

        foreach ($files as $file) {
            if (!file_exists($file)) {
                continue;
            }

            // todo Handle symlink.
            if (is_dir($file)) {
                self::remove(new \FilesystemIterator($file, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS),
                    $removeEmptyParentDirs);
                self::removeDirectory($file);
            } else {
                if (@unlink($file) === false) {
                    throw new IOException(sprintf('Failed to remove file "%s".', $file), $file);
                }

                $i = $removeEmptyParentDirs;

                while ($i-- > 0 && !array_diff(scandir($parentDir = dirname($file)), ['.', '..'])) {
                    self::removeDirectory($file = $parentDir);
                }
            }
        }
    }


    private static function removeDirectory(string $directory)
    {
        if (@rmdir($directory) === false) {
            throw new IOException(sprintf('Failed to remove directory "%s".', $directory), $directory);
        }
    }

    /**
     * Creates a directory recursively if does not exist.
     *
     * @param string $directory The directory path.
     * @param int $mode
     *
     * @throws IOException
     */
    public static function createDirectory(string $directory, int $mode = 0777)
    {
        if (is_dir($directory)) {
            return;
        }

        if (@mkdir($directory, $mode, true) === false) {
            throw new IOException(sprintf('Failed to create directory "%s".', $directory), $directory);
        }
    }

    /**
     * Recursively scans a directory.
     *
     * @param string $directory The path to the directory.
     *
     * @return Directory
     */
    public static function scanDirectory(string $directory): Directory
    {
        $_directory = new Directory($directory);
        $iterator = new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS);

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                $_directory->addDirectory(self::scanDirectory($file));
            } else {
                $_directory->addFile($file);
            }
        }

        return $_directory;
    }
}
