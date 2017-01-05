<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto;

class RoutingAutoEvents
{
    /**
     * Dispatched after the adapter has created a new auto route
     * The event class is Symfony\Cmf\Component\RoutingAuto\Event\AutoRouteCreateEvent.
     */
    const POST_CREATE = 'cmf_routing_auto.auto_route.post_create';

    /**
     * Dispatched after the adapter has migrated children from an old route to a new one
     * The event class is Symfony\Cmf\Component\RoutingAuto\Event\AutoRouteMigrateEvent.
     */
    const POST_MIGRATE = 'cmf_routing_auto.auto_route.post_migrate';
}
