<?php

namespace Victor78\ZippyExt\Archive;

use Alchemy\Zippy\Archive\Archive as BaseArchive;
use Alchemy\Zippy\Adapter\AdapterInterface;
use Alchemy\Zippy\Resource\ResourceManager;
use Alchemy\Zippy\Adapter\Resource\ResourceInterface;

/**
 * Represents an archive
 */
class Archive extends BaseArchive implements \Alchemy\Zippy\Archive\ArchiveInterface
{
    /**
     * @inheritdoc
     */
    public function extract($toDirectory, $password = null)
    {
        if ($password) {
            $this->adapter->setPassword($password);
        }
        $this->adapter->extract($this->resource, $toDirectory);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function extractMembers($members, $toDirectory = null, $password = null)
    {
        if ($password) {
            $this->adapter->setPassword($password);
        }
        
        $this->adapter->extractMembers($this->resource, $members, $toDirectory);

        return $this;
    }
}
