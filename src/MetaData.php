<?php declare(strict_types = 1);

namespace Radiergummi\FileSystem;

use DateTimeImmutable;
use SplFileInfo;

class MetaData
{
    protected int $size;

    protected DateTimeImmutable $lastModifiedTime;

    protected ?DateTimeImmutable $createdTime;

    protected ?array $meta;

    public function __construct(
        int $size,
        DateTimeImmutable $lastModifiedTime,
        ?DateTimeImmutable $createdTime = null,
        ?array $meta = null
    ) {
        $this->size = $size;
        $this->lastModifiedTime = $lastModifiedTime;
        $this->createdTime = $createdTime;
        $this->meta = $meta;
    }

    public static function fromFileInfo(SplFileInfo $fileInfo): self
    {
        return new static(
            $fileInfo->getSize(),
            (new DateTimeImmutable())->setTimestamp($fileInfo->getMTime()),
            (new DateTimeImmutable())->setTimestamp($fileInfo->getCTime()),
            [
                'owner' => $fileInfo->getOwner(),
                'permissions' => $fileInfo->getPerms(),
                'inode' => $fileInfo->getInode(),
                'type' => $fileInfo->getType(),
            ]
        );
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getLastModifiedTime(): DateTimeImmutable
    {
        return $this->lastModifiedTime;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getCreatedTime(): ?DateTimeImmutable
    {
        return $this->createdTime;
    }

    /**
     * @return array|null
     */
    public function getMeta(): ?array
    {
        return $this->meta;
    }
}
