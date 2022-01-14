<?php

namespace Insitaction\ManagersBundle\Manager\Request;

use Insitaction\ManagersBundle\Manager\Request\Adapter\RequestAdapterInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

class RequestManager implements RequestManagerInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function getAdapter(string $adapterClassname): RequestAdapterInterface
    {
        if (!class_exists($adapterClassname)) {
            throw new RuntimeException(sprintf('The service "%s" has an adapter set to "%s", but this is not a valid class. Check your class naming.', RequestAdapterInterface::class, $adapterClassname));
        }

        if ($this->container->has($adapterClassname)) {
            $adapter = $this->container->get($adapterClassname);

            if (!$adapter instanceof RequestAdapterInterface) {
                throw new RuntimeException(sprintf('The service "%s" must implement RequestAdapterInterface.', $adapterClassname));
            }

            return $adapter;
        }

        if (is_a($adapterClassname, RequestAdapterInterface::class, true)) {
            throw new RuntimeException(sprintf('The service "%s" implements "%s", but its service could not be found.', $adapterClassname, RequestAdapterInterface::class));
        }

        throw new RuntimeException(sprintf('The adapter "%s" does not exist.', $adapterClassname));
    }
}
