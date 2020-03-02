<?php declare(strict_types = 1);

namespace Radiergummi\FileSystem;

use Generator;
use Psr\Http\Message\StreamInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Radiergummi\FileSystem\Behaviour\CacheAwareTrait;
use Radiergummi\FileSystem\Behaviour\RootPathGuardingTrait;
use Radiergummi\FileSystem\Entities\File;
use Radiergummi\FileSystem\Exceptions\RootViolationException;
use Radiergummi\FileSystem\Interfaces\AdapterInterface;
use Radiergummi\FileSystem\Interfaces\FileSystemInterface;
use RuntimeException;

use function basename;
use function dirname;

/**
 * Default FileSystemInterface implementation.
 *
 * @package Radiergummi\FileSystem
 */
class FileSystem implements FileSystemInterface
{
    use CacheAwareTrait;
    use RootPathGuardingTrait;

    protected AdapterInterface $adapter;

    /**
     * FileSystem constructor.
     *
     * @param AdapterInterface    $adapter  File system adapter to connect to the storage with.
     * @param CacheInterface|null $cache    A generic cache to store files in.
     * @param string|null         $rootPath Root path to limit the file system to.
     */
    public function __construct(AdapterInterface $adapter, ?CacheInterface $cache = null, ?string $rootPath = null)
    {
        $this->adapter = $adapter;
        $this->setRootPath($rootPath);
        $this->setCache($cache);
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
     * @throws InvalidArgumentException
     */
    public function readFile(string $path): ?File
    {
        $path = $this->normalizePath($path);

        if ($this->cache && $this->cache->has($path)) {
            return $this->cache->get($path);
        }

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
     * @return Generator
     * @throws Exceptions\EntityIsDirectoryException
     * @throws Exceptions\EntityIsNoDirectoryException
     * @throws Exceptions\EntityNotAccessibleException
     * @throws Exceptions\EntityNotFoundException
     * @throws RootViolationException
     */
    public function readDirectory(?string $path = null, bool $recursive = false): Generator
    {
        $path = $this->normalizePath($path);

        yield from $this->adapter->readDirectory($path);
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
    public function writeFile(string $path, StreamInterface $contents, ?int $flags = null): void
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
    public function deleteFile(string $path): void
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

        $this->checkRootPathViolation($path);

        return $path;
    }
}
