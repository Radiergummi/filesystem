<?php declare(strict_types = 1);

namespace Radiergummi\FileSystem\Exceptions;

use RuntimeException;
use Throwable;

abstract class FileSystemException extends RuntimeException
{
    public const CODE_EACCES = 13;

    public const CODE_EEXIST = 17;

    public const CODE_EISDIR = 21;

    public const CODE_ENOENT = 2;

    public const CODE_ENOTDIR = 20;

    public const MESSAGE_EACCES = 'Permission denied';

    public const MESSAGE_EEXIST = 'File exists';

    public const MESSAGE_EISDIR = 'Is a directory';

    public const MESSAGE_ENOENT = 'No such file or directory';

    public const MESSAGE_ENOTDIR = 'Not a directory';

    protected string $path;

    public function __construct(string $path, ?string $message = null, ?int $code = null, ?Throwable $previous = null)
    {
        parent::__construct($message ?? "Failed to access {$path}", $code ?? 0, $previous);

        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
