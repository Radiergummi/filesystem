<?php declare(strict_types = 1);

namespace Radiergummi\FileSystem;

use Psr\Http\Message\StreamInterface;
use Radiergummi\FileSystem\Entities\Directory;
use Radiergummi\FileSystem\Entities\File;
use Radiergummi\FileSystem\Exceptions\RootViolationException;
use RuntimeException;

use function basename;
use function dirname;

class FileSystem implements FileSystemInterface
{
    protected AdapterInterface $adapter;

    protected ?string $rootPath;

    public function __construct(AdapterInterface $adapter, ?string $rootPath = null)
    {
        $this->adapter = $adapter;
        $this->rootPath = $rootPath;
    }

    /**
     * Checks whether a file exists
     *
     * @param string $path
     *
     * @return bool
     * @throws RootViolationException
     * @throws Exceptions\EntityNotAccessibleException
     */
    public function exists(string $path): bool
    {
        if (! $path) {
            return false;
        }

        $path = $this->normalizePath($path);

        return $this->adapter->exists($path);
    }

    /**
     * Reads a file
     *
     * @param string $path
     *
     * @return File|null
     * @throws Exceptions\EntityIsDirectoryException
     * @throws Exceptions\EntityNotAccessibleException
     * @throws Exceptions\EntityNotFoundException
     * @throws RootViolationException
     */
    public function read(string $path): ?File
    {
        $path = $this->normalizePath($path);

        return $this->adapter->read($path);
    }

    /**
     * Retrieves metadata about a file
     *
     * @param string $path
     *
     * @return MetaData|null
     * @throws Exceptions\EntityNotAccessibleException
     * @throws Exceptions\EntityNotFoundException
     * @throws RootViolationException
     */
    public function getMetaData(string $path): ?MetaData
    {
        $path = $this->normalizePath($path);

        return $this->adapter->stat($path);
    }

    /**
     * Reads a directory
     *
     * @param string|null $path
     * @param bool        $recursive
     *
     * @return Directory|null
     * @throws Exceptions\EntityIsNoDirectoryException
     * @throws Exceptions\EntityNotAccessibleException
     * @throws Exceptions\EntityNotFoundException
     * @throws RootViolationException
     */
    public function readDirectory(?string $path = null, bool $recursive = false): ?Directory
    {
        $path = $this->normalizePath($path);

        return $this->adapter->readDirectory($path);
    }

    /**
     * Writes a file
     *
     * @param string          $path
     * @param StreamInterface $contents
     * @param int|null        $flags
     *
     * @throws Exceptions\EntityIsDirectoryException
     * @throws Exceptions\EntityNotAccessibleException
     * @throws Exceptions\EntityNotFoundException
     * @throws RootViolationException
     * @throws RuntimeException
     */
    public function write(string $path, StreamInterface $contents, ?int $flags = null): void
    {
        $path = $this->normalizePath($path);

        $this->adapter->write($path, $contents, $flags);
    }

    /**
     * Renames a file or directory
     *
     * @param string $path
     * @param string $newName
     *
     * @throws Exceptions\EntityExistsException
     * @throws Exceptions\EntityIsDirectoryException
     * @throws Exceptions\EntityIsNoDirectoryException
     * @throws Exceptions\EntityNotAccessibleException
     * @throws Exceptions\EntityNotFoundException
     * @throws RootViolationException
     */
    public function rename(string $path, string $newName): void
    {
        $sourcePath = $this->normalizePath($path);
        $destinationPath = joinPath(dirname($sourcePath), basename($newName));

        $this->adapter->move($sourcePath, $destinationPath);
    }

    /**
     * Copies a file or directory
     *
     * @param string $sourcePath
     * @param string $destinationPath
     *
     * @throws Exceptions\EntityExistsException
     * @throws Exceptions\EntityIsDirectoryException
     * @throws Exceptions\EntityIsNoDirectoryException
     * @throws Exceptions\EntityNotAccessibleException
     * @throws Exceptions\EntityNotFoundException
     * @throws RootViolationException
     */
    public function copy(string $sourcePath, string $destinationPath): void
    {
        $sourcePath = $this->normalizePath($sourcePath);
        $destinationPath = $this->normalizePath($destinationPath);

        $this->adapter->copy($sourcePath, $destinationPath);
    }

    /**
     * Moves a file or directory
     *
     * @param string $sourcePath
     * @param string $destinationPath
     *
     * @throws Exceptions\EntityExistsException
     * @throws Exceptions\EntityIsDirectoryException
     * @throws Exceptions\EntityIsNoDirectoryException
     * @throws Exceptions\EntityNotAccessibleException
     * @throws Exceptions\EntityNotFoundException
     * @throws RootViolationException
     */
    public function move(string $sourcePath, string $destinationPath): void
    {
        $sourcePath = $this->normalizePath($sourcePath);
        $destinationPath = $this->normalizePath($destinationPath);

        $this->adapter->move($sourcePath, $destinationPath);
    }

    /**
     * Deletes a file
     *
     * @param string $path
     *
     * @throws Exceptions\EntityIsDirectoryException
     * @throws Exceptions\EntityNotAccessibleException
     * @throws Exceptions\EntityNotFoundException
     * @throws RootViolationException
     */
    public function delete(string $path): void
    {
        $path = $this->normalizePath($path);

        $this->adapter->delete($path);
    }

    /**
     * Deletes a directory
     *
     * @param string $path
     *
     * @throws Exceptions\EntityIsNoDirectoryException
     * @throws Exceptions\EntityNotAccessibleException
     * @throws Exceptions\EntityNotFoundException
     * @throws RootViolationException
     */
    public function deleteDirectory(string $path): void
    {
        $path = $this->normalizePath($path);

        $this->adapter->deleteDirectory($path);
    }

    /**
     * Creates a directory
     *
     * @param string $path
     *
     * @throws Exceptions\EntityExistsException
     * @throws Exceptions\EntityIsNoDirectoryException
     * @throws Exceptions\EntityNotAccessibleException
     * @throws RootViolationException
     */
    public function createDirectory(string $path): void
    {
        $path = $this->normalizePath($path);

        $this->adapter->createDirectory($path);
    }

    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    /**
     * Normalizes and merges a file path with the root path
     *
     * @param string $path
     *
     * @return string
     * @throws RootViolationException
     */
    final protected function normalizePath(string $path): string
    {
        $path = joinPath($this->rootPath, $path);
        $path = normalizePath($path);

        if (! pathStartsWith($path, $this->rootPath)) {
            throw new RootViolationException($path, $this->rootPath);
        }

        return $path;
    }
}
