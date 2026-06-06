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

        return $this;
    }

    /**
     * Set the deflator to unzip
     */
    public function setDeflator(ProcessBuilderFactoryInterface $deflator): static
    {
        $this->deflator = $deflator;

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

        $processInflate = $this->inflator->create()->getProcess();
        $processInflate->run();

        if (false === $processInflate->isSuccessful()) {
            return $this->isSupported = VersionProbeInterface::PROBE_NOTSUPPORTED;
        }

        $output = $processInflate->getOutput();
        $inflatorOk = false !== stripos($output, '7-Zip');


        return $this->isSupported = $inflatorOk
            ? VersionProbeInterface::PROBE_OK
            : VersionProbeInterface::PROBE_NOTSUPPORTED;
    }
}
