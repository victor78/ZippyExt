<?php

declare(strict_types=1);

namespace Victor78\ZippyExt\Adapter\VersionProbe;

use Alchemy\Zippy\ProcessBuilder\ProcessBuilderFactoryInterface;
use Alchemy\Zippy\Adapter\VersionProbe\VersionProbeInterface;

class Zip7zipVersionProbe implements VersionProbeInterface
{
    private ?int $isSupported = null;

    public function __construct(
        private ProcessBuilderFactoryInterface $inflator,
        private ProcessBuilderFactoryInterface $deflator
    ) {}

    /**
     * Set the inflator to zip
     */
    public function setInflator(ProcessBuilderFactoryInterface $inflator): static
    {
        $this->inflator = $inflator;
        $this->isSupported = null;

        return $this;
    }

    /**
     * Set the deflator to unzip
     */
    public function setDeflator(ProcessBuilderFactoryInterface $deflator): static
    {
        $this->deflator = $deflator;
        $this->isSupported = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(): int
    {
        if ($this->isSupported !== null) {
            return $this->isSupported;
        }

        $inflatorOk = $this->isFactorySupported($this->inflator);
        $deflatorOk = $this->isFactorySupported($this->deflator);

        return $this->isSupported = ($inflatorOk && $deflatorOk)
            ? VersionProbeInterface::PROBE_OK
            : VersionProbeInterface::PROBE_NOTSUPPORTED;
    }

    private function isFactorySupported(ProcessBuilderFactoryInterface $factory): bool
    {
        $process = $factory->create()->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            return false;
        }

        return stripos($process->getOutput(), '7-Zip') !== false;
    }
}
