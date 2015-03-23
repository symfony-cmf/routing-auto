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

class AutoRouteCreateEvent extends Event
{
    private $uriContext;
    private $autoRoute;

    public function __construct(AutoRouteInterface $autoRoute, UriContext $uriContext)
    {
        $this->uriContext = $uriContext;
    }

    public function getUriContext()
    {
        return $this->uriContext;
    }

    public function getAutoRoute()
    {
        return $this->autoRoute;
    }
}
