<?php

declare(strict_types=1);

namespace Victor78\ZippyExt;

use Alchemy\Zippy\Exception\ExceptionInterface;
use Alchemy\Zippy\Exception\RuntimeException;
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
use Alchemy\Zippy\Archive\ArchiveInterface;

class Zippy extends \Alchemy\Zippy\Zippy
{
    public static function load(): static
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

    private function sanitizeExtension(string $extension): string
    {
        return ltrim(trim(mb_strtolower($extension)), '.');
    }

    /**
     * Creates an archive.
     *
     * @param string                         $path
     * @param string|array|\Traversable|null $files
     * @param bool                           $recursive
     * @param string|null                    $type
     * @param string|null                    $password
     *
     * @throws RuntimeException In case of failure
     */
    public function create($path, $files = null, $recursive = true, $type = null, $password = null)
    {
        if (null === $type) {
            $type = $this->guessAdapterExtension($path);
        }

        if ($type === null) {
            throw new RuntimeException(sprintf('Unable to guess archive type from path "%s"', (string) $path));
        }

        try {
            $adapter = $this->getAdapterFor($this->sanitizeExtension((string) $type));
            if (method_exists($adapter, 'setPassword')) {
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
     * @param string      $path
     * @param string|null $type
     * @param string|null $password
     *
     * @throws RuntimeException In case of failure
     */
    public function open($path, $type = null, $password = null)
    {
        if (null === $type) {
            $type = $this->guessAdapterExtension($path);
        }

        if ($type === null) {
            throw new RuntimeException(sprintf('Unable to guess archive type from path "%s"', (string) $path));
        }

        try {
            $adapter = $this->getAdapterFor($this->sanitizeExtension((string) $type));
            if (method_exists($adapter, 'setPassword')) {
                $adapter->setPassword($password);
            }
            return $adapter->open($path);
        } catch (ExceptionInterface $e) {
            throw new RuntimeException('Unable to open archive', $e->getCode(), $e);
        }
    }

    /**
     * Finds an extension that has a strategy registered given a file path.
     * Returns null if no matching strategy found.
     */
    private function guessAdapterExtension(string $path): ?string
    {
        $path = strtolower(trim($path));
        foreach ($this->getStrategies() as $extension => $strategy) {
            if (str_ends_with($path, $extension)) {
                return $extension;
            }
        }

        return null;
    }
}
