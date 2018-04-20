<?php

namespace Victor78\ZippyExt\Adapter;

use Alchemy\Zippy\Adapter\{
    ZipAdapter,
    ZipExtensionAdapter,
    BSDTar\TarBSDTarAdapter,
    BSDTar\TarBz2BSDTarAdapter,
    BSDTar\TarGzBSDTarAdapter,
    GNUTar\TarBz2GNUTarAdapter,
    GNUTar\TarGNUTarAdapter,
    GNUTar\TarGzGNUTarAdapter,
    AdapterContainer as OldAdapterContainer
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
    public static function load()
    {
        $container = new static();

        $container['zip.inflator'] = null;
        $container['zip.deflator'] = null;

        $container['resource-manager'] = function($container) {
            return new ResourceManager(
                $container['request-mapper'],
                $container['resource-teleporter'],
                $container['filesystem']
            );
        };

        $container['executable-finder'] = function($container) {
            return new ExecutableFinder();
        };

        $container['request-mapper'] = function($container) {
            return new RequestMapper($container['target-locator']);
        };

        $container['target-locator'] = function() {
            return new TargetLocator();
        };

        $container['teleporter-container'] = function($container) {
            return TeleporterContainer::load();
        };

        $container['resource-teleporter'] = function($container) {
            return new ResourceTeleporter($container['teleporter-container']);
        };

        $container['filesystem'] = function() {
            return new Filesystem();
        };

        $container['Alchemy\\Zippy\\Adapter\\ZipAdapter'] = function($container) {
            return ZipAdapter::newInstance(
                $container['executable-finder'],
                $container['resource-manager'],
                $container['zip.inflator'],
                $container['zip.deflator']
            );
        };

        $container['gnu-tar.inflator'] = null;
        $container['gnu-tar.deflator'] = null;

        $container['Alchemy\\Zippy\\Adapter\\GNUTar\\TarGNUTarAdapter'] = function($container) {
            return TarGNUTarAdapter::newInstance(
                $container['executable-finder'],
                $container['resource-manager'],
                $container['gnu-tar.inflator'],
                $container['gnu-tar.deflator']
            );
        };

        $container['Alchemy\\Zippy\\Adapter\\GNUTar\\TarGzGNUTarAdapter'] = function($container) {
            return TarGzGNUTarAdapter::newInstance(
                $container['executable-finder'],
                $container['resource-manager'],
                $container['gnu-tar.inflator'],
                $container['gnu-tar.deflator']
            );
        };

        $container['Alchemy\\Zippy\\Adapter\\GNUTar\\TarBz2GNUTarAdapter'] = function($container) {
            return TarBz2GNUTarAdapter::newInstance(
                $container['executable-finder'],
                $container['resource-manager'],
                $container['gnu-tar.inflator'],
                $container['gnu-tar.deflator']
            );
        };

        $container['bsd-tar.inflator'] = null;
        $container['bsd-tar.deflator'] = null;

        $container['Alchemy\\Zippy\\Adapter\\BSDTar\\TarBSDTarAdapter'] = function($container) {
            return TarBSDTarAdapter::newInstance(
                $container['executable-finder'],
                $container['resource-manager'],
                $container['bsd-tar.inflator'],
                $container['bsd-tar.deflator']
            );
        };

        $container['Alchemy\\Zippy\\Adapter\\BSDTar\\TarGzBSDTarAdapter'] = function($container) {
            return TarGzBSDTarAdapter::newInstance(
                $container['executable-finder'],
                $container['resource-manager'],
                $container['bsd-tar.inflator'],
                $container['bsd-tar.deflator']
            );
        };

        $container['Alchemy\\Zippy\\Adapter\\BSDTar\\TarBz2BSDTarAdapter'] = function($container) {
            return TarBz2BSDTarAdapter::newInstance(
                $container['executable-finder'],
                $container['resource-manager'],
                $container['bsd-tar.inflator'],
                $container['bsd-tar.deflator']);
        };

        $container['Alchemy\\Zippy\\Adapter\\ZipExtensionAdapter'] = function() {
            return ZipExtensionAdapter::newInstance();
        };
        
        $container['7zip.inflator'] = null;
        $container['7zip.deflator'] = null;
        
        $container['Victor78\\ZippyExt\\Adapter\\Zip7zipAdapter'] = function($container) {
            return \Victor78\ZippyExt\Adapter\Zip7zipAdapter::newInstance(
                $container['executable-finder'],
                $container['resource-manager'],
                $container['7zip.inflator'],
                $container['7zip.deflator']
            );
        }; 
        return $container;
    }   
}