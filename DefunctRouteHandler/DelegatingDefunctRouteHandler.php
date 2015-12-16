<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandler;

use Symfony\Cmf\Component\RoutingAuto\Mapping\MetadataFactory;
use Symfony\Cmf\Component\RoutingAuto\AdapterInterface;
use Symfony\Cmf\Component\RoutingAuto\ServiceRegistry;
use Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandlerInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContextCollection;

/**
 * Defunct route handler which delegates the handling of
 * defunct routes based on the mapped classes confiugration.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class DelegatingDefunctRouteHandler implements DefunctRouteHandlerInterface
{
    protected $serviceRegistry;
    protected $adapter;

    /**
     * @param ServiceRegistry auto routing service registry (for getting old route action)
     * @param AdapterInterface auto routing backend adapter
     * @param MetadataFactory  auto routing metadata factory
     */
    public function __construct(
        MetadataFactory $metadataFactory,
        AdapterInterface $adapter,
        ServiceRegistry $serviceRegistry
    ) {
        $this->serviceRegistry = $serviceRegistry;
        $this->adapter = $adapter;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function handleDefunctRoutes(UriContextCollection $uriContextCollection)
    {
        $subject = $uriContextCollection->getSubjectObject();
        $realClassName = $this->adapter->getRealClassName(get_class($uriContextCollection->getSubjectObject()));
        $metadata = $this->metadataFactory->getMetadataForClass($realClassName);

        $defunctRouteHandlerConfig = $metadata->getDefunctRouteHandler();

        $defunctHandler = $this->serviceRegistry->getDefunctRouteHandler($defunctRouteHandlerConfig['name']);
        $defunctHandler->handleDefunctRoutes($uriContextCollection);
    }
}
