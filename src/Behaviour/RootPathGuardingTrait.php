<?php declare(strict_types = 1);

namespace Radiergummi\FileSystem\Behaviour;

use Radiergummi\FileSystem\Exceptions\RootViolationException;

use function Radiergummi\FileSystem\pathStartsWith;

trait RootPathGuardingTrait
{
    private ?string $rootPath = null;

    /**
     * @param string|null $rootPath
     */
    public function setRootPath(?string $rootPath): void
    {
        $this->rootPath = $rootPath;
    }

    /**
     * @return string|null
     */
    protected function getRootPath(): ?string
    {
        return $this->rootPath;
    }

    /**
     * Checks whether a path violates the root path
     *
     * @param string $path
     *
     * @throws RootViolationException
     */
    protected function checkRootPathViolation(string $path): void
    {
        $rootPath = $this->getRootPath();

        if (! $rootPath) {
            return;
        }

        if (! pathStartsWith($path, $this->rootPath)) {
            throw new RootViolationException($path, $this->rootPath);
        }
    }
}
