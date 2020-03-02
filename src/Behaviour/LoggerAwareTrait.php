<?php declare(strict_types = 1);

namespace Radiergummi\FileSystem\Behaviour;

use Psr\Log\LoggerInterface;

trait LoggerAwareTrait
{
    private ?LoggerInterface $logger = null;

    public function setLogger(?LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    protected function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }
}
