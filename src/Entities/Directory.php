<?php declare(strict_types = 1);

namespace Radiergummi\FileSystem\Entities;

use Exception;
use Generator;
use IteratorAggregate;
use Radiergummi\FileSystem\MetaData;
use Traversable;

class Directory extends FileSystemEntity implements IteratorAggregate
{
    protected Generator $generator;

    /**
     * @var MetaData|null
     */
    protected ?MetaData $metaData;

    public function __construct(Generator $generator, ?MetaData $metaData)
    {
        $this->generator = $generator;
        $this->metaData = $metaData;
    }

    public function getStats(): MetaData
    {
        return $this->metaData;
    }

    /**
     * Retrieves the directory children
     *
     * @return Traversable
     * @throws Exception
     */
    public function getChildren(): Traversable
    {
        return $this->getIterator();
    }

    public function getIterator(): Traversable
    {
        yield from $this->generator;
    }
}
