<?php

namespace Victor78\ZippyExt\FileStrategy;

class Zip7zaFileStrategy extends \Alchemy\Zippy\FileStrategy\AbstractFileStrategy
{
 
    /**
     * {@inheritdoc}
     */
    protected function getServiceNames()
    {
        return array(
            'Victor78\\ZippyExt\\Adapter\\Zip7zaAdapter',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFileExtension()
    {
        return '7za';
    }
}
