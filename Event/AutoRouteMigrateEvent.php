<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;

/**
 * Event that is dispatched when an auto route is migrated (moved)
 * from one place to another.
 */
class AutoRouteMigrateEvent extends Event
{
    /**
     * @var AutoRouteInterface
     */
    private $srcAutoRoute;

    /**
     * @var AutoRouteInterface
     */
    private $destAutoRoute;

    /**
     * @param AutoRouteInterface $srcAutoRoute
     * @param AutoRouteInterface $destAutoRoute
     */
    public function __construct(AutoRouteInterface $srcAutoRoute, AutoRouteInterface $destAutoRoute)
    {
        $this->srcAutoRoute = $srcAutoRoute;
        $this->destAutoRoute = $destAutoRoute;
    }

    /**
     * Return the source (original) auto route.
     *
     * @return AutoRouteInterface
     */
    public function getSrcAutoRoute()
    {
        return $this->srcAutoRoute;
    }

    /**
     * Return the destination (new) auto route.
     *
     * @return AutoRouteInterface
     */
    public function getDestAutoRoute()
    {
        return $this->destAutoRoute;
    }
}
