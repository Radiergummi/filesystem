<?php declare(strict_types = 1);

namespace Radiergummi\FileSystem\Adapters;

use DirectoryIterator;
use Generator;
use InvalidArgumentException;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Radiergummi\FileSystem\Entities\File;
use Radiergummi\FileSystem\Exceptions\EntityExistsException;
use Radiergummi\FileSystem\Exceptions\EntityIsDirectoryException;
use Radiergummi\FileSystem\Exceptions\EntityIsNoDirectoryException;
use Radiergummi\FileSystem\Exceptions\EntityNotAccessibleException;
use Radiergummi\FileSystem\Exceptions\EntityNotFoundException;
use Radiergummi\FileSystem\Interfaces\AdapterInterface;
use Radiergummi\FileSystem\MetaData;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Sunrise\Stream\StreamFactory;
use Throwable;
use UnexpectedValueException;

use function copy;
use function dirname;
use function error_clear_last;
use function error_get_last;
use function file_exists;
use function is_dir;
use function is_writable;
use function rename;

class LocalAdapter implements AdapterInterface
{
    /**
     * @var StreamFactoryInterface
     */
    private StreamFactoryInterface $streamFactory;

    public function __construct(?StreamFactoryInterface $streamFactory = null)
    {
        $this->streamFactory = $streamFactory ?? new StreamFactory();
    }

    /**
     * @inheritDoc
     */
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * @inheritDoc
     */
    public function read(string $path): ?File
    {
        try {
            $contents = $this->streamFactory->createStreamFromFile($path);
            $metaData = $this->stat($path);
        } catch (Throwable $exception) {
            throw new EntityNotAccessibleException($path, $exception);
        }

        return new File(
            $path,
            $contents,
            $metaData
        );
    }

    /**
     * @inheritDoc
     */
    public function stat(string $path): ?MetaData
    {
        $fileInfo = new SplFileInfo($path);

        return MetaData::fromFileInfo($fileInfo);
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     */
    public function readDirectory(?string $path = null, bool $recursive = false): Generator
    {
        $this->assertEntityExists($path);
        $this->assertEntityIsDirectory($path);

        try {
            $files = $recursive
                ? $this->listDirectoryRecursively($path)
                : $this->listDirectory($path);
        } catch (Throwable $exception) {
            throw new EntityNotAccessibleException($path, $exception);
        }

        foreach ($files as $file) {
            $filePath = $file->getRealPath();

            if ($file->isDir()) {
                if ($recursive) {
                    yield $filePath => $this->readDirectory($filePath);
                }

                continue;
            }

            yield $filePath => $this->read($filePath);
        }
    }

    /**
     * @inheritDoc
     * @throws RuntimeException
     */
    public function write(string $path, StreamInterface $contents, ?int $flags = null): void
    {
        $this->assertParentExists($path);
        $this->assertEntityIsWritable($path);
        $this->assertEntityIsNoDirectory($path);

        $contents->rewind();

        $this->writeStream($path, $contents);
    }

    /**
     * @inheritDoc
     */
    public function copy(string $sourcePath, string $destinationPath, bool $override = false): void
    {
        if (! $override && $this->exists($destinationPath)) {
            throw new EntityExistsException($destinationPath);
        }

        copy($sourcePath, $destinationPath);
    }

    /**
     * @inheritDoc
     */
    public function move(string $sourcePath, string $destinationPath, bool $override = false): void
    {
        if (! $override && $this->exists($destinationPath)) {
            throw new EntityExistsException($destinationPath);
        }

        rename($sourcePath, $destinationPath);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $path): void
    {
        if (! $this->exists($path)) {
            throw new EntityNotFoundException($path);
        }

        if (is_dir($path)) {
            throw new EntityIsDirectoryException($path);
        }

        $this->unlinkFile($path);
    }

    /**
     * @inheritDoc
     * @throws UnexpectedValueException
     */
    public function deleteDirectory(string $path): void
    {
        $this->assertEntityExists($path);
        $this->assertEntityIsDirectory($path);

        $files = $this->listDirectoryRecursively($path, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if ($file->isDir()) {
                $this->deleteDirectory($file->getRealPath());

                continue;
            }

            $this->unlinkFile($file->getRealPath());
        }

        unset($files);

        $this->unlinkDirectory($path);
    }

    /**
     * @inheritDoc
     */
    public function createDirectory(string $path): void
    {
        if ($this->exists($path)) {
            throw new EntityExistsException($path);
        }

        try {
            $this->wrapCall('mkdir', $path);
        } catch (RuntimeException $exception) {
            throw new EntityNotAccessibleException($path, $exception);
        }
    }

    /**
     * Writes a stream
     *
     * @param string          $path
     * @param StreamInterface $stream
     *
     * @throws RuntimeException
     */
    private function writeStream(string $path, StreamInterface $stream): void
    {
        $sourceStream = $stream->detach();
        $destinationStream = $this->wrapCall('fopen', $path, 'w+b');

        $this->wrapCall('stream_copy_to_stream', $sourceStream, $destinationStream);
        $this->wrapCall('fclose', $destinationStream);
    }

    /**
     * Wraps a low-level call in error handling
     *
     * @param string $function
     * @param mixed  ...$args
     *
     * @return mixed
     * @throws RuntimeException
     */
    private function wrapCall(string $function, ...$args)
    {
        error_clear_last();

        $result = @$function(...$args);

        if ($result) {
            return $result;
        }

        $error = error_get_last();

        throw new RuntimeException($error['message'] ?? 'Unknown error');
    }

    /**
     * Lists the contents of a directory recursively
     *
     * @param string $path
     * @param int    $mode
     *
     * @return Generator<SplFileInfo>
     * @throws UnexpectedValueException
     */
    private function listDirectoryRecursively(string $path, int $mode = RecursiveIteratorIterator::SELF_FIRST): Generator
    {
        $directoryIterator = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);

        yield from new RecursiveIteratorIterator($directoryIterator, $mode);
    }

    /**
     * Lists a directory non-recursively
     *
     * @param string $path
     *
     * @return Generator
     * @throws RuntimeException
     * @throws UnexpectedValueException
     */
    private function listDirectory(string $path): Generator
    {
        $iterator = new DirectoryIterator($path);

        foreach ($iterator as $item) {
            if ($item->isDot()) {
                continue;
            }

            yield $item;
        }
    }

    /**
     * Asserts an entity exists or throws.
     *
     * @param string $path
     *
     * @throws EntityNotFoundException
     */
    private function assertEntityExists(string $path): void
    {
        if (! file_exists($path)) {
            throw new EntityNotFoundException($path);
        }
    }

    /**
     * Asserts an entity exists or throws.
     *
     * @param string $path
     *
     * @throws EntityNotFoundException
     * @throws EntityIsNoDirectoryException
     */
    private function assertParentExists(string $path): void
    {
        $directory = dirname($path);

        if (! file_exists($directory)) {
            throw new EntityNotFoundException($directory);
        }

        if (! is_dir($directory)) {
            throw new EntityIsNoDirectoryException($directory);
        }
    }

    /**
     * Asserts an entity is writable or throws.
     *
     * @param string $path
     *
     * @throws EntityNotAccessibleException
     */
    private function assertEntityIsWritable(string $path): void
    {
        if (! is_writable($path)) {
            throw new EntityNotAccessibleException($path);
        }
    }

    /**
     * Asserts an entity is not a directory or throws.
     *
     * @param string $path
     *
     * @throws EntityIsDirectoryException
     */
    private function assertEntityIsNoDirectory(string $path): void
    {
        if (is_dir($path)) {
            throw new EntityIsDirectoryException($path);
        }
    }

    /**
     * Asserts an entity is a directory or throws.
     *
     * @param string $path
     *
     * @throws EntityIsNoDirectoryException
     */
    private function assertEntityIsDirectory(string $path): void
    {
        if (! is_dir($path)) {
            throw new EntityIsNoDirectoryException($path);
        }
    }

    /**
     * Unlinks a file
     *
     * @param string $path
     *
     * @throws EntityNotAccessibleException
     */
    private function unlinkFile(string $path): void
    {
        try {
            $this->wrapCall('unlink', $path);
        } catch (RuntimeException $exception) {
            throw new EntityNotAccessibleException($path, $exception);
        }
    }

    /**
     * Unlinks a directory
     *
     * @param string $path
     *
     * @throws EntityNotAccessibleException
     */
    private function unlinkDirectory(string $path): void
    {
        try {
            $this->wrapCall('rmdir', $path);
        } catch (RuntimeException $exception) {
            throw new EntityNotAccessibleException($path, $exception);
        }
    }
}
