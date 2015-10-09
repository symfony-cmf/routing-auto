<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandler;

use Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandlerInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContextCollection;
use Symfony\Cmf\Component\RoutingAuto\AdapterInterface;

class LeaveRedirectDefunctRouteHandler implements DefunctRouteHandlerInterface
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * {@inheritDoc}
     */
    public function handleDefunctRoutes(UriContextCollection $uriContextCollection)
    {
        $referringAutoRouteCollection = $this->adapter->getReferringAutoRoutes($uriContextCollection->getSubjectObject());

        foreach ($referringAutoRouteCollection as $referringAutoRoute) {
            if (true === $uriContextCollection->containsAutoRoute($referringAutoRoute)) {
                continue;
            }

            $newRoute = $uriContextCollection->getAutoRouteByTag($referringAutoRoute->getAutoRouteTag());

                if (null === $newRoute) {
                    continue;
                }

                $this->adapter->migrateAutoRouteChildren($referringAutoRoute, $newRoute);
                $this->adapter->createRedirectRoute($referringAutoRoute, $newRoute);

            $this->adapter->createRedirectRoute($referringAutoRoute, $newRoute);
        }
    }
}
