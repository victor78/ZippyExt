<?php

declare(strict_types=1);

namespace Victor78\ZippyExt\FileStrategy;

class Zip7zipFileStrategy extends \Alchemy\Zippy\FileStrategy\AbstractFileStrategy
{
    /**
     * {@inheritdoc}
     */
    protected function getServiceNames(): array
    {
        return [
            'Victor78\\ZippyExt\\Adapter\\Zip7zipAdapter',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFileExtension(): string
    {
        return '7zip';
    }
}
