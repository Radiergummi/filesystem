<?php declare(strict_types = 1);

namespace Radiergummi\FileSystem\Exceptions;

use Throwable;

class EntityNotAccessibleException extends FileSystemException
{
    public function __construct(string $path, ?Throwable $previous = null)
    {
        parent::__construct(
            $path,
            FileSystemException::MESSAGE_EACCES,
            FileSystemException::CODE_EACCES,
            $previous
        );
    }
}
