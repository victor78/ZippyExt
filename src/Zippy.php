<?php

namespace Victor78\ZippyExt;

use Victor78\ZippyExt\FileStrategy\Zip7zipFileStrategy;
use Alchemy\Zippy\FileStrategy\{
    ZipFileStrategy,
    TarFileStrategy,
    TarGzFileStrategy,
    TarBz2FileStrategy,
    TB2FileStrategy,
    TBz2FileStrategy,
    TGzFileStrategy
};


class Zippy extends \Alchemy\Zippy\Zippy
{
    public static function load()
    {
        $adapters = Adapter\AdapterContainer::load();


        $factory = new static($adapters);

        $factory->addStrategy(new ZipFileStrategy($adapters));
        $factory->addStrategy(new TarFileStrategy($adapters));
        $factory->addStrategy(new TarGzFileStrategy($adapters));
        $factory->addStrategy(new TarBz2FileStrategy($adapters));
        $factory->addStrategy(new TB2FileStrategy($adapters));
        $factory->addStrategy(new TBz2FileStrategy($adapters));
        $factory->addStrategy(new TGzFileStrategy($adapters));
        $factory->addStrategy(new Zip7zipFileStrategy($adapters));
        return $factory;
    }   
    
    private function sanitizeExtension($extension)
    {
        return ltrim(trim(mb_strtolower($extension)), '.');
    }
    /**
     * Creates an archive
     *
     * @param string                         $path
     * @param string|array|\Traversable|null $files
     * @param bool                           $recursive
     * @param string|null                    $type
     *
     * @return ArchiveInterface
     *
     * @throws RuntimeException In case of failure
     */
    public function create($path, $files = null, $recursive = true, $type = null, $password = null)
    {
        if (null === $type) {
            $type = $this->guessAdapterExtension($path);
        }

        
        
        try {
            $adapter = $this->getAdapterFor($this->sanitizeExtension($type));
            if (method_exists($adapter, 'setPassword') && $password){
                $adapter->setPassword($password);
            }
            return $adapter->create($path, $files, $recursive);
        } catch (ExceptionInterface $e) {
            throw new RuntimeException('Unable to create archive', $e->getCode(), $e);
        }
    }    

    /**
     * Opens an archive.
     *
     * @param string $path
     *
     * @return ArchiveInterface
     *
     * @throws RuntimeException In case of failure
     */
    public function open($path, $type = null, $password = null)
    {
        if (null === $type) {
            $type = $this->guessAdapterExtension($path);
        }

        try {
            $adapter = $this->getAdapterFor($this->sanitizeExtension($type));
            if (method_exists($adapter, 'setPassword') && $password){
                $adapter->setPassword($password);
            }
            return $adapter->open($path);
        } catch (ExceptionInterface $e) {
            throw new RuntimeException('Unable to open archive', $e->getCode(), $e);
        }
    }
    
    /**
     * Finds an extension that has strategy registered given a file path
     *
     * Returns null if no matching strategy found.
     *
     * @param string $path
     *
     * @return string|null
     */
    private function guessAdapterExtension($path)
    {
        $path = strtolower(trim($path));
        foreach ($this->getStrategies() as $extension => $strategy) {
            if ($extension === substr($path, (strlen($extension) * -1))) {
                return $extension;
            }
        }

        return null;
    }    
}
