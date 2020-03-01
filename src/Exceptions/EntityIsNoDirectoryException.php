<?php declare(strict_types = 1);

namespace Radiergummi\FileSystem\Exceptions;

use Throwable;

class EntityIsNoDirectoryException extends FileSystemException
{
    public function __construct(string $path, ?Throwable $previous = null)
    {
        parent::__construct(
            $path,
            FileSystemException::MESSAGE_ENOTDIR,
            FileSystemException::CODE_ENOTDIR,
            $previous
        );
    }
}

