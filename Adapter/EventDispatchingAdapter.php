<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Adapter;

use Symfony\Cmf\Component\RoutingAuto\AdapterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContext;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Cmf\Component\RoutingAuto\RoutingAutoEvents;
use Symfony\Cmf\Component\RoutingAuto\Event\AutoRouteCreateEvent;
use Symfony\Cmf\Component\RoutingAuto\Event\AutoRouteMigrateEvent;

/**
 * This adapter wraps a concrete adapter and dispatches events.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class EventDispatchingAdapter implements AdapterInterface
{
    private $dispatcher;

    public function __construct(AdapterInterface $adapter, EventDispatcherInterface $eventDispatcher)
    {
        $this->adapter = $adapter;
        $this->dispatcher = $eventDispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function getLocales($contentDocument)
    {
        return $this->adapter->getLocales($contentDocument);
    }

    /**
     * {@inheritDoc}
     */
    public function translateObject($contentDocument, $locale)
    {
        return $this->adapter->translateObject($contentDocument, $locale);
    }

    /**
     * {@inheritDoc}
     */
    public function generateAutoRouteTag(UriContext $uriContext)
    {
        return $this->adapter->generateAutoRouteTag($uriContext);
    }

    /**
     * {@inheritDoc}
     */
    public function migrateAutoRouteChildren(AutoRouteInterface $srcAutoRoute, AutoRouteInterface $destAutoRoute)
    {
        $this->adapter->migrateAutoRouteChildren($srcAutoRoute, $destAutoRoute);
        $this->dispatcher->dispatch(RoutingAutoEvents::POST_MIGRATE, new AutoRouteMigrateEvent($srcAutoRoute, $destAutoRoute));
    }

    /**
     * {@inheritDoc}
     */
    public function removeAutoRoute(AutoRouteInterface $autoRoute)
    {
        $this->adapter->removeAutoRoute($autoRoute);
    }

    /**
     * {@inheritDoc}
     */
    public function createAutoRoute(UriContext $uriContext, $contentDocument, $autoRouteTag)
    {
        $autoRoute = $this->adapter->createAutoRoute($uriContext, $contentDocument, $autoRouteTag);
        $this->dispatcher->dispatch(RoutingAutoEvents::POST_CREATE, new AutoRouteCreateEvent($autoRoute, $uriContext));

        return $autoRoute;
    }

    /**
     * {@inheritDoc}
     */
    public function createRedirectRoute(AutoRouteInterface $referringAutoRoute, AutoRouteInterface $newRoute)
    {
        $this->adapter->createRedirectRoute($referringAutoRoute, $newRoute);
    }

    /**
     * {@inheritDoc}
     */
    public function getRealClassName($className)
    {
        return $this->adapter->getRealClassName($className);
    }

    /**
     * {@inheritDoc}
     */
    public function compareAutoRouteContent(AutoRouteInterface $autoRoute, $contentDocument)
    {
        return $this->adapter->compareAutoRouteContent($autoRoute, $contentDocument);
    }

    /**
     * {@inheritDoc}
     */
    public function getReferringAutoRoutes($contentDocument)
    {
        return $this->adapter->getReferringAutoRoutes($contentDocument);
    }

    /**
     * {@inheritDoc}
     */
    public function findRouteForUri($uri, UriContext $uriContext)
    {
        return $this->adapter->findRouteForUri($uri, $uriContext);
    }
}
