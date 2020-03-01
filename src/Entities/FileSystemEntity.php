<?php declare(strict_types = 1);

namespace Radiergummi\FileSystem\Entities;

use Radiergummi\FileSystem\MetaData;

abstract class FileSystemEntity
{
    abstract public function getStats(): MetaData;
}
