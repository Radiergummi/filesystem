<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types = 1);

namespace Radiergummi\FileSystem\Tests\Adapters;

use PHPUnit\Framework\TestCase;
use Radiergummi\FileSystem\Adapters\LocalAdapter;
use Radiergummi\FileSystem\Entities\File;
use Radiergummi\FileSystem\Entities\FileSystemEntity;
use Radiergummi\FileSystem\Exceptions\EntityIsDirectoryException;
use Radiergummi\FileSystem\Exceptions\EntityNotAccessibleException;
use Radiergummi\FileSystem\Exceptions\EntityNotFoundException;
use Radiergummi\FileSystem\FileSystem;
use Sunrise\Stream\StreamFactory;

use function chmod;
use function touch;

class LocalAdapterTest extends TestCase
{

    public function testMove(): void
    {
    }

    public function testDeleteDirectory(): void
    {
    }

    public function testReadDirectory(): void
    {
    }

    public function testWrite(): void
    {
    }

    public function testWriteBailsOnMissingPath(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionCode(2);
        $adapter = new LocalAdapter();
        $fileSystem = new FileSystem($adapter, __DIR__ . '/../fixtures');
        $streamFactory = new StreamFactory();

        $fileSystem->write('foo/bar/baz', $streamFactory->createStream('foo'));
    }

    public function testWriteBailsOnDirectory(): void
    {
        $this->expectException(EntityIsDirectoryException::class);
        $this->expectExceptionCode(21);
        $adapter = new LocalAdapter();
        $fileSystem = new FileSystem($adapter, realpath(__DIR__ . '/..'));
        $streamFactory = new StreamFactory();

        $fileSystem->write('.', $streamFactory->createStream('foo'));
    }

    public function testWriteBailsOnInsufficientPermissions(): void
    {
        $this->expectException(EntityNotAccessibleException::class);
        $this->expectExceptionCode(13);
        $adapter = new LocalAdapter();
        $fileSystem = new FileSystem($adapter, __DIR__ . '/../fixtures');
        $streamFactory = new StreamFactory();

        touch(__DIR__ . '/../fixtures/test_eaccess.file');
        chmod(__DIR__ . '/../fixtures/test_eaccess.file', 0);

        $fileSystem->write('test_eaccess.file', $streamFactory->createStream('foo'));
    }

    public function testRead(): void
    {
        $adapter = new LocalAdapter();
        $fileSystem = new FileSystem($adapter, __DIR__ . '/../fixtures');
        $file = $fileSystem->read('foo.txt');

        $this->assertInstanceOf(File::class, $file);
        $this->assertInstanceOf(FileSystemEntity::class, $file);
        $this->assertSame("foo bar baz\n", $file->getStream()->getContents());
    }

    public function testDelete(): void
    {
    }

    public function testExists(): void
    {
    }

    public function testCopy(): void
    {
    }

    public function testStat(): void
    {
    }

    public function testCreateDirectory(): void
    {
    }
}
