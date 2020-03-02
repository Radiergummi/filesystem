<?php declare(strict_types = 1);

namespace Radiergummi\FileSystem\Behaviour;

use Psr\SimpleCache\CacheInterface;

trait CacheAwareTrait
{
    private ?CacheInterface $cache = null;

    public function setCache(?CacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    protected function getCache(): ?CacheInterface
    {
        return $this->cache;
    }
}
