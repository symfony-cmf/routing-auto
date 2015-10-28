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

class LeaveRedirectDefunctRouteHandler implements DefunctRouteHandlerInterface, ConfigurableInterface
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolverInterface $optionsResolver)
    {
        $optionsResolver->setDefault('http_code', 301);
    }

    /**
     * {@inheritDoc}
     */
    public function handleDefunctRoutes(UriContextCollection $uriContextCollection, array $options = array())
    {
        $referringAutoRouteCollection = $this->adapter->getReferringAutoRoutes($uriContextCollection->getSubjectObject());

        foreach ($referringAutoRouteCollection as $referringAutoRoute) {
            if (false === $uriContextCollection->containsAutoRoute($referringAutoRoute)) {
                $newRoute = $uriContextCollection->getAutoRouteByTag($referringAutoRoute->getAutoRouteTag());

                if (null === $newRoute) {
                    continue;
                }

                $this->adapter->migrateAutoRouteChildren($referringAutoRoute, $newRoute);
                $this->adapter->createRedirectRoute($referringAutoRoute, $newRoute);
            }
        }
    }
}

