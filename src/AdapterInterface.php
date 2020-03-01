<?php declare(strict_types = 1);

namespace Radiergummi\FileSystem;

use Generator;
use Psr\Http\Message\StreamInterface;
use Radiergummi\FileSystem\Entities\File;
use Radiergummi\FileSystem\Entities\FileSystemEntity;
use Radiergummi\FileSystem\Exceptions\EntityExistsException;
use Radiergummi\FileSystem\Exceptions\EntityIsDirectoryException;
use Radiergummi\FileSystem\Exceptions\EntityIsNoDirectoryException;
use Radiergummi\FileSystem\Exceptions\EntityNotAccessibleException;
use Radiergummi\FileSystem\Exceptions\EntityNotFoundException;

interface AdapterInterface
{
    public const FILE_APPEND = 1;

    public const FILE_LOCK = 2;

    /**
     * Check whether a file or directory exists.
     *
     * @param string $path
     *
     * @return bool
     * @throws EntityNotAccessibleException
     */
    public function exists(string $path): bool;

    /**
     * Read a file.
     *
     * @param string $path The path to the file.
     *
     * @return File|null The file.
     * @throws EntityNotFoundException
     * @throws EntityNotAccessibleException
     * @throws EntityIsDirectoryException
     */
    public function read(string $path): ?File;

    /**
     * Reads meta data of a file or directory.
     *
     * @param string $path
     *
     * @return MetaData|null
     * @throws EntityNotFoundException
     * @throws EntityNotAccessibleException
     */
    public function stat(string $path): ?MetaData;

    /**
     * List contents of a directory.
     *
     * @param string|null $path      Path to the directory to list.
     * @param bool        $recursive Whether to list recursively.
     *
     * @return Generator<string, FileSystemEntity> A generator wrapping the directory contents.
     * @throws EntityNotFoundException
     * @throws EntityNotAccessibleException
     * @throws EntityIsNoDirectoryException
     * @throws EntityIsDirectoryException
     */
    public function readDirectory(?string $path = null, bool $recursive = false): Generator;

    /**
     * Writes to a file.
     *
     * @param string          $path     The path of the new file.
     * @param StreamInterface $contents The file contents.
     * @param int|null        $flags
     *
     * @return void
     * @throws EntityNotFoundException
     * @throws EntityNotAccessibleException
     * @throws EntityIsDirectoryException
     */
    public function write(string $path, StreamInterface $contents, ?int $flags = null): void;

    /**
     * Copies a file or directory.
     *
     * @param string $sourcePath
     * @param string $destinationPath
     *
     * @return void
     * @throws EntityNotFoundException
     * @throws EntityExistsException
     * @throws EntityNotAccessibleException
     * @throws EntityIsDirectoryException
     * @throws EntityIsNoDirectoryException
     */
    public function copy(string $sourcePath, string $destinationPath): void;

    /**
     * Moves a file or directory.
     *
     * @param string $sourcePath
     * @param string $destinationPath
     *
     * @return void
     * @throws EntityNotFoundException
     * @throws EntityExistsException
     * @throws EntityNotAccessibleException
     * @throws EntityIsDirectoryException
     * @throws EntityIsNoDirectoryException
     */
    public function move(string $sourcePath, string $destinationPath): void;

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @return void
     * @throws EntityNotFoundException
     * @throws EntityNotAccessibleException
     * @throws EntityIsDirectoryException
     */
    public function delete(string $path): void;

    /**
     * Deletes a directory.
     *
     * @param string $path
     *
     * @return void
     * @throws EntityNotFoundException
     * @throws EntityNotAccessibleException
     * @throws EntityIsNoDirectoryException
     */
    public function deleteDirectory(string $path): void;

    /**
     * Creates a directory.
     *
     * @param string $path
     *
     * @return void
     * @throws EntityExistsException
     * @throws EntityNotAccessibleException
     */
    public function createDirectory(string $path): void;
}
