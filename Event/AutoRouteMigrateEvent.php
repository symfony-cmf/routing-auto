<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Cmf\Component\RoutingAuto\UriContext;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;

class AutoRouteMigrateEvent extends Event
{
    private $srcAutoRoute;
    private $destAutoRoute;

    public function __construct(AutoRouteInterface $srcAutoRoute, AutoRouteInterface $destAutoRoute)
    {
        $this->srcAutoRoute = $srcAutoRoute;
        $this->destAutoRoute = $destAutoRoute;
    }

    public function getSrcAutoRoute() 
    {
        return $this->srcAutoRoute;
    }

    public function getDestAutoRoute() 
    {
        return $this->destAutoRoute;
    }
}
