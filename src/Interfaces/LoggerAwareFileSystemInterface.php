<?php declare(strict_types = 1);

namespace Radiergummi\FileSystem\Interfaces;

use Psr\Log\LoggerInterface;

interface LoggerAwareFileSystemInterface extends FileSystemInterface
{
    public function setLogger(LoggerInterface $logger): void;
}
