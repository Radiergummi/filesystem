<?php declare(strict_types = 1);

namespace Radiergummi\FileSystem\Interfaces;

use Generator;
use Psr\Http\Message\StreamInterface;
use Radiergummi\FileSystem\Entities\Directory;
use Radiergummi\FileSystem\Entities\File;
use Radiergummi\FileSystem\MetaData;

interface FileSystemInterface
{
    /**
     * Creates a new file system.
     *
     * @param AdapterInterface $adapter File system adapter to connect to the storage with.
     */
    public function __construct(AdapterInterface $adapter);

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists(string $path): bool;

    /**
     * Read a file.
     *
     * @param string $path The path to the file.
     *
     * @return File|null The file.
     */
    public function readFile(string $path): ?File;

    /**
     * Reads meta data of a file or directory.
     *
     * @param string $path
     *
     * @return MetaData|null
     */
    public function getMetaData(string $path): ?MetaData;

    /**
     * List contents of a directory.
     *
     * @param string $directory The directory to list.
     * @param bool   $recursive Whether to list recursively.
     *
     * @return Directory|null The directory.
     */
    public function readDirectory(?string $directory = null, bool $recursive = false): Generator;

    /**
     * Writes to a file.
     *
     * @param string          $path     The path of the new file.
     * @param StreamInterface $contents The file contents.
     * @param int|null        $flags
     *
     * @return void
     */
    public function writeFile(string $path, StreamInterface $contents, ?int $flags = null): void;

    /**
     * Renames a file.
     *
     * @param string $sourcePath
     * @param string $newName
     *
     * @return void
     */
    public function rename(string $sourcePath, string $newName): void;

    /**
     * Copies a file.
     *
     * @param string $sourcePath
     * @param string $destinationPath
     *
     * @return void
     */
    public function copy(string $sourcePath, string $destinationPath): void;

    /**
     * Moves a file.
     *
     * @param string $sourcePath
     * @param string $destinationPath
     *
     * @return void
     */
    public function move(string $sourcePath, string $destinationPath): void;

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @return void
     */
    public function deleteFile(string $path): void;

    /**
     * Deletes a directory.
     *
     * @param string $path
     *
     * @return void
     */
    public function deleteDirectory(string $path): void;

    /**
     * Creates a directory.
     *
     * @param string $path
     *
     * @return void
     */
    public function createDirectory(string $path): void;
}
