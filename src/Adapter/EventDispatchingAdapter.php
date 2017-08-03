<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Adapter;

use Symfony\Cmf\Component\RoutingAuto\AdapterInterface;
use Symfony\Cmf\Component\RoutingAuto\Event\AutoRouteCreateEvent;
use Symfony\Cmf\Component\RoutingAuto\Event\AutoRouteMigrateEvent;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Cmf\Component\RoutingAuto\RoutingAutoEvents;
use Symfony\Cmf\Component\RoutingAuto\UriContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This adapter wraps a concrete adapter and dispatches events.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class EventDispatchingAdapter implements AdapterInterface
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(AdapterInterface $adapter, EventDispatcherInterface $eventDispatcher)
    {
        $this->adapter = $adapter;
        $this->dispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocales($contentDocument)
    {
        return $this->adapter->getLocales($contentDocument);
    }

    /**
     * {@inheritdoc}
     */
    public function translateObject($contentDocument, $locale)
    {
        return $this->adapter->translateObject($contentDocument, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function generateAutoRouteTag(UriContext $uriContext)
    {
        return $this->adapter->generateAutoRouteTag($uriContext);
    }

    /**
     * {@inheritdoc}
     */
    public function migrateAutoRouteChildren(AutoRouteInterface $srcAutoRoute, AutoRouteInterface $destAutoRoute)
    {
        $this->adapter->migrateAutoRouteChildren($srcAutoRoute, $destAutoRoute);
        $this->dispatcher->dispatch(RoutingAutoEvents::POST_MIGRATE, new AutoRouteMigrateEvent($srcAutoRoute, $destAutoRoute));
    }

    /**
     * {@inheritdoc}
     */
    public function removeAutoRoute(AutoRouteInterface $autoRoute)
    {
        $this->adapter->removeAutoRoute($autoRoute);
    }

    /**
     * {@inheritdoc}
     */
    public function createAutoRoute(UriContext $uriContext, $autoRouteTag)
    {
        $autoRoute = $this->adapter->createAutoRoute($uriContext, $autoRouteTag);
        $this->dispatcher->dispatch(RoutingAutoEvents::POST_CREATE, new AutoRouteCreateEvent($autoRoute, $uriContext));

        return $autoRoute;
    }

    /**
     * {@inheritdoc}
     */
    public function createRedirectRoute(AutoRouteInterface $referringAutoRoute, AutoRouteInterface $newRoute)
    {
        $this->adapter->createRedirectRoute($referringAutoRoute, $newRoute);
    }

    /**
     * {@inheritdoc}
     */
    public function getRealClassName($className)
    {
        return $this->adapter->getRealClassName($className);
    }

    /**
     * {@inheritdoc}
     */
    public function compareAutoRouteContent(AutoRouteInterface $autoRoute, $contentDocument)
    {
        return $this->adapter->compareAutoRouteContent($autoRoute, $contentDocument);
    }

    /**
     * {@inheritdoc}
     */
    public function compareAutoRouteLocale(AutoRouteInterface $autoRoute, $locale)
    {
        return $this->adapter->compareAutoRouteLocale($autoRoute, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getReferringAutoRoutes($contentDocument)
    {
        return $this->adapter->getReferringAutoRoutes($contentDocument);
    }

    /**
     * {@inheritdoc}
     */
    public function findRouteForUri($uri, UriContext $uriContext)
    {
        return $this->adapter->findRouteForUri($uri, $uriContext);
    }
}
