<?php declare(strict_types = 1);

namespace Radiergummi\FileSystem\Exceptions;

use Throwable;

class EntityIsDirectoryException extends FileSystemException
{
    public function __construct(string $path, ?Throwable $previous = null)
    {
        parent::__construct(
            $path,
            FileSystemException::MESSAGE_EISDIR,
            FileSystemException::CODE_EISDIR,
            $previous
        );
    }
}

