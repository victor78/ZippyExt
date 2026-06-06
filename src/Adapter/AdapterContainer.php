<?php

declare(strict_types=1);

namespace Victor78\ZippyExt\Adapter;

use Alchemy\Zippy\Adapter\{
    AdapterContainer as OldAdapterContainer,
    BSDTar\TarBz2BSDTarAdapter,
    BSDTar\TarBSDTarAdapter,
    BSDTar\TarGzBSDTarAdapter,
    GNUTar\TarBz2GNUTarAdapter,
    GNUTar\TarGNUTarAdapter,
    GNUTar\TarGzGNUTarAdapter,
    ZipAdapter,
    ZipExtensionAdapter
};
use Alchemy\Zippy\Resource\{
    RequestMapper,
    ResourceManager,
    ResourceTeleporter,
    TargetLocator,
    TeleporterContainer
};
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ExecutableFinder;

class AdapterContainer extends OldAdapterContainer
{
    public static function load(): static
    {
        $container = new static();

        $container['zip.inflator'] = null;
        $container['zip.deflator'] = null;

        $container['resource-manager'] = static function ($container) {
            return new ResourceManager(
                $container['request-mapper'],
                $container['resource-teleporter'],
                $container['filesystem']
            );
        };

        $container['executable-finder'] = static function () {
            return new ExecutableFinder();
        };

        $container['request-mapper'] = static function ($container) {
            return new RequestMapper($container['target-locator']);
        };

        $container['target-locator'] = static function () {
            return new TargetLocator();
        };

        $container['teleporter-container'] = static function () {
            return TeleporterContainer::load();
        };

        $container['resource-teleporter'] = static function ($container) {
            return new ResourceTeleporter($container['teleporter-container']);
        };

        $container['filesystem'] = static function () {
            return new Filesystem();
        };

        $container['Alchemy\\Zippy\\Adapter\\ZipAdapter'] = static function ($container) {
            return ZipAdapter::newInstance(
                $container['executable-finder'],
                $container['resource-manager'],
                $container['zip.inflator'],
                $container['zip.deflator']
            );
        };

        $container['gnu-tar.inflator'] = null;
        $container['gnu-tar.deflator'] = null;

        $container['Alchemy\\Zippy\\Adapter\\GNUTar\\TarGNUTarAdapter'] = static function ($container) {
            return TarGNUTarAdapter::newInstance(
                $container['executable-finder'],
                $container['resource-manager'],
                $container['gnu-tar.inflator'],
                $container['gnu-tar.deflator']
            );
        };

        $container['Alchemy\\Zippy\\Adapter\\GNUTar\\TarGzGNUTarAdapter'] = static function ($container) {
            return TarGzGNUTarAdapter::newInstance(
                $container['executable-finder'],
                $container['resource-manager'],
                $container['gnu-tar.inflator'],
                $container['gnu-tar.deflator']
            );
        };

        $container['Alchemy\\Zippy\\Adapter\\GNUTar\\TarBz2GNUTarAdapter'] = static function ($container) {
            return TarBz2GNUTarAdapter::newInstance(
                $container['executable-finder'],
                $container['resource-manager'],
                $container['gnu-tar.inflator'],
                $container['gnu-tar.deflator']
            );
        };

        $container['bsd-tar.inflator'] = null;
        $container['bsd-tar.deflator'] = null;

        $container['Alchemy\\Zippy\\Adapter\\BSDTar\\TarBSDTarAdapter'] = static function ($container) {
            return TarBSDTarAdapter::newInstance(
                $container['executable-finder'],
                $container['resource-manager'],
                $container['bsd-tar.inflator'],
                $container['bsd-tar.deflator']
            );
        };

        $container['Alchemy\\Zippy\\Adapter\\BSDTar\\TarGzBSDTarAdapter'] = static function ($container) {
            return TarGzBSDTarAdapter::newInstance(
                $container['executable-finder'],
                $container['resource-manager'],
                $container['bsd-tar.inflator'],
                $container['bsd-tar.deflator']
            );
        };

        $container['Alchemy\\Zippy\\Adapter\\BSDTar\\TarBz2BSDTarAdapter'] = static function ($container) {
            return TarBz2BSDTarAdapter::newInstance(
                $container['executable-finder'],
                $container['resource-manager'],
                $container['bsd-tar.inflator'],
                $container['bsd-tar.deflator']
            );
        };

        $container['Alchemy\\Zippy\\Adapter\\ZipExtensionAdapter'] = static function () {
            return ZipExtensionAdapter::newInstance();
        };

        $container['7zip.inflator'] = null;
        $container['7zip.deflator'] = null;

        $container['Victor78\\ZippyExt\\Adapter\\Zip7zipAdapter'] = static function ($container) {
            return Zip7zipAdapter::newInstance(
                $container['executable-finder'],
                $container['resource-manager'],
                $container['7zip.inflator'],
                $container['7zip.deflator']
            );
        };

        return $container;
    }
}