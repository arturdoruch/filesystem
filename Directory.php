<?php

namespace ArturDoruch\Filesystem;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class Directory extends \SplFileInfo
{
    /**
     * @var \SplFileInfo[]
     */
    private $files = [];

    /**
     * @var Directory[]
     */
    private $directories = [];

    /**
     * @param string $path Path to the directory.
     */
    public function __construct(string $path)
    {
        if (!is_dir($path)) {
            throw new \InvalidArgumentException(sprintf('Invalid directory path "%s".', $path));
        }

        parent::__construct($path);
    }

    /**
     * {@inheritdoc}
     */
    public function isDir()
    {
        return true;
    }

    /**
     * Adds file of the directory.
     *
     * @param \SplFileInfo $file
     */
    public function addFile(\SplFileInfo $file)
    {
        if ($file->isDir()) {
            throw new \InvalidArgumentException('Unable to set directory as file.');
        }

        $this->files[] = $file;
    }

    /**
     * @return \SplFileInfo[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Adds sub directory.
     *
     * @param Directory $directory
     */
    public function addDirectory(Directory $directory)
    {
        $this->directories[] = $directory;
    }

    /**
     * @return Directory[] The sub directories.
     */
    public function getDirectories(): array
    {
        return $this->directories;
    }
}
