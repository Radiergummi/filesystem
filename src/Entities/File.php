<?php declare(strict_types = 1);

namespace Radiergummi\FileSystem\Entities;

use Psr\Http\Message\StreamInterface;
use Radiergummi\FileSystem\MetaData;
use RuntimeException;

use function basename;
use function dirname;
use function pathinfo;

use const PATHINFO_EXTENSION;

class File extends FileSystemEntity
{
    protected string $path;

    protected StreamInterface $stream;

    protected ?MetaData $metaData = null;

    public function __construct(string $path, StreamInterface $stream, ?MetaData $metaData = null)
    {
        $this->path = $path;
        $this->stream = $stream;
        $this->metaData = $metaData;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getBaseName(): string
    {
        return basename($this->getPath());
    }

    public function getDirectory(?int $levels = null): string
    {
        return dirname($this->getPath(), $levels);
    }

    public function getExtension(): string
    {
        return pathinfo($this->getPath(), PATHINFO_EXTENSION);
    }

    public function getStream(): StreamInterface
    {
        return $this->stream;
    }

    public function getMetaData(): ?MetaData
    {
        return $this->metaData;
    }

    /**
     * Stringifies the stream
     *
     * @return string
     * @throws RuntimeException
     */
    public function __toString(): string
    {
        $stream = $this->getStream();

        $stream->rewind();

        return $stream->getContents();
    }
}
