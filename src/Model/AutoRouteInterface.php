<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
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
    public const TYPE_PRIMARY = 'cmf_routing_auto.primary';

    /**
     * Redirect auto route should be used to redirect to
     * a different route (i.e. it should be used to represent
     * old URIs which should redirect to new URIs).
     */
    public const TYPE_REDIRECT = 'cmf_routing_auto.redirect';

    /**
     * Set a locale related to this auto route.
     *
     * @param string $locale
     */
    public function setLocale($locale);

    /**
     * Return the locale.
     *
     * @return string
     */
    public function getLocale();

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
     * @param AutoRouteInterface AutoRoute to redirect to
     */
    public function setRedirectTarget($autoTarget);

    /**
     * Return the redirect target (when the auto route is of type
     * REDIRECT).
     *
     * @return AutoRouteInterface
     */
    public function getRedirectTarget();
}
