<?php

declare(strict_types=1);

namespace Victor78\ZippyExt\Archive;

use Alchemy\Zippy\Archive\Archive as BaseArchive;
use Alchemy\Zippy\Archive\ArchiveInterface;

/**
 * Represents an archive with optional password support.
 */
class Archive extends BaseArchive implements ArchiveInterface
{
    public function extract($toDirectory, ?string $password = null): static
    {
        if ($password !== null && method_exists($this->adapter, 'setPassword')) {
            $this->adapter->setPassword($password);
        }
        $this->adapter->extract($this->resource, $toDirectory);

        return $this;
    }

    public function extractMembers($members, $toDirectory = null, ?string $password = null): static
    {
        if ($password !== null && method_exists($this->adapter, 'setPassword')) {
            $this->adapter->setPassword($password);
        }
        $this->adapter->extractMembers($this->resource, $members, $toDirectory);

        return $this;
    }
}
