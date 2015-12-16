<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandler;

use Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandlerInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContextCollection;
use Symfony\Cmf\Component\RoutingAuto\AdapterInterface;

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
        $referringAutoRouteCollection = $this->adapter->getReferringAutoRoutes($uriContextCollection->getSubjectObject());

        foreach ($referringAutoRouteCollection as $referringAutoRoute) {
            if (false === $uriContextCollection->containsAutoRoute($referringAutoRoute)) {
                $newRoute = $uriContextCollection->getAutoRouteByTag($referringAutoRoute->getAutoRouteTag());

                if (null !== $newRoute) {
                    $this->adapter->migrateAutoRouteChildren($referringAutoRoute, $newRoute);
                }

                $this->adapter->removeAutoRoute($referringAutoRoute);
            }
        }
    }
}
