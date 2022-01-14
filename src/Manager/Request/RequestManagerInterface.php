<?php

namespace Insitaction\ManagersBundle\Manager\Request;

use Insitaction\ManagersBundle\Manager\Request\Adapter\RequestAdapterInterface;
use Psr\Container\ContainerInterface;

interface RequestManagerInterface
{
    public function __construct(ContainerInterface $container);

    public function getAdapter(string $adapterClassname): RequestAdapterInterface;
}
