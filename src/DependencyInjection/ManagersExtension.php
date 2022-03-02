<?php

namespace Insitaction\ManagersBundle\DependencyInjection;

use Insitaction\ManagersBundle\Manager\Import\ImportInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ManagersExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $container->registerForAutoconfiguration(ImportInterface::class)
            ->addTag('insitaction.manager.import')
        ;
    }
}
