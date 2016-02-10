<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Model;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * Interface to be implemented by objects which represent
 * auto routes.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface AutoRouteInterface extends RouteObjectInterface
{
    /**
     * Primary auto route represents the content directly.
     */
    const TYPE_PRIMARY = 'cmf_routing_auto.primary';

    /**
     * Redirect auto route should be used to redirect to
     * a different route (i.e. it should be used to represent
     * old URIs which should redirect to new URIs).
     */
    const TYPE_REDIRECT = 'cmf_routing_auto.redirect';

    /**
     * Set a tag which can be used by a database implementation
     * to distinguish a route from other routes as required.
     *
     * @param string $tag
     */
    public function setAutoRouteTag($tag);

    /**
     * Return the auto route tag.
     *
     * @return string
     */
    public function getAutoRouteTag();

    /**
     * Set the auto route mode.
     *
     * Should be one of AutoRouteInterface::TYPE_* constants
     *
     * @param string $mode
     */
    public function setType($mode);

    /**
     * For use in the REDIRECT mode, specifies the routable object
     * that the AutoRoute should redirect to.
     *
     * @param AutoRouteInterface AutoRoute to redirect to.
     */
    public function setRedirectTarget(AutoRouteInterface $autoTarget);

    /**
     * Return the redirect target (when the auto route is of type
     * REDIRECT).
     *
     * @return AutoRouteInterface
     */
    public function getRedirectTarget();
}
