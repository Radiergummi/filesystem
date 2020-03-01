<?php declare(strict_types = 1);

namespace Radiergummi\FileSystem\Exceptions;

class RootViolationException extends FileSystemException
{
    protected string $path;

    protected string $rootPath;

    public function __construct(string $path, string $rootPath)
    {
        parent::__construct($path, 'Path outside root path');

        $this->rootPath = $rootPath;
    }

    /**
     * @return string
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }
}
