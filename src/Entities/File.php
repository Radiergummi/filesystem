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

    protected ?StreamInterface $stream = null;

    protected ?MetaData $metaData = null;

    /**
     * @var callable
     */
    protected $streamGetter;

    /**
     * @var callable
     */
    protected $metaDataGetter;

    public function __construct(string $path, callable $streamGetter, callable $metaDataGetter)
    {
        $this->path = $path;
        $this->streamGetter = $streamGetter;
        $this->metaDataGetter = $metaDataGetter;
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
        if (! $this->stream) {
            $getter = $this->streamGetter;

            $this->stream = $getter();
        }

        return $this->stream;
    }

    public function getStats(): MetaData
    {
        if (! $this->metaData) {
            $getter = $this->metaDataGetter;

            $this->metaData = $getter();
        }

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
