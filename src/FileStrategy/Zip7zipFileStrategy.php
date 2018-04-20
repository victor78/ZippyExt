<?php

namespace Victor78\ZippyExt\FileStrategy;

class Zip7zipFileStrategy extends \Alchemy\Zippy\FileStrategy\AbstractFileStrategy
{
 
    /**
     * {@inheritdoc}
     */
    protected function getServiceNames()
    {
        return array(
            'Victor78\\ZippyExt\\Adapter\\Zip7zipAdapter',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFileExtension()
    {
        return '7zip';
    }
}
