<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandler;

use Symfony\Cmf\Component\RoutingAuto\AdapterInterface;
use Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandlerInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContextCollection;

class RemoveDefunctRouteHandler implements DefunctRouteHandlerInterface
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @param AdapterInterface
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function handleDefunctRoutes(UriContextCollection $uriContextCollection)
    {
        $referringAutoRouteCollection = $this->adapter->getReferringAutoRoutes($uriContextCollection->getSubject());

        foreach ($referringAutoRouteCollection as $referringAutoRoute) {
            if (false === $uriContextCollection->containsAutoRoute($referringAutoRoute)) {
                $newRoute = $uriContextCollection->getAutoRouteByLocale($referringAutoRoute->getLocale());

                if (null !== $newRoute) {
                    $this->adapter->migrateAutoRouteChildren($referringAutoRoute, $newRoute);
                }

                $this->adapter->removeAutoRoute($referringAutoRoute);
            }
        }
    }
}
