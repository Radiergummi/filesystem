<?php declare(strict_types = 1);

namespace Radiergummi\FileSystem\Interfaces;

use Psr\SimpleCache\CacheInterface;

interface CacheAwareFileSystemInterface extends FileSystemInterface
{
    public function setCache(CacheInterface $cache): void;
}
