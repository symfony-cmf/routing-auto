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
use Symfony\Cmf\Component\RoutingAuto\UriContext;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;

/**
 * Event that is dispatched after an auto route has been created
 */
class AutoRouteCreateEvent extends Event
{
    /**
     * @var UriContext
     */
    private $uriContext;

    /**
     * @var AutoRouteInterface
     */
    private $autoRoute;

    /**
     * @param AutoRouteInterface $autoRoute
     * @param UriContext $uriContext
     */
    public function __construct(AutoRouteInterface $autoRoute, UriContext $uriContext)
    {
        $this->uriContext = $uriContext;
        $this->autoRoute = $autoRoute;
    }

    /**
     * Return the URI context
     *
     * @return UriContext
     */
    public function getUriContext()
    {
        return $this->uriContext;
    }

    /**
     * Return the newly created auto route implementation
     *
     * @return AutoRouteInterface
     */
    public function getAutoRoute()
    {
        return $this->autoRoute;
    }
}
