<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit;

use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Cmf\Component\RoutingAuto\ConflictResolverInterface;
use Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandlerInterface;
use Symfony\Cmf\Component\RoutingAuto\ServiceRegistry;
use Symfony\Cmf\Component\RoutingAuto\TokenProviderInterface;

class ServiceRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceRegistry
     */
    private $serviceRegistry;

    /**
     * @var TokenProviderInterface|ObjectProphecy
     */
    private $tokenProvider;

    /**
     * @var ConflictResolverInterface|ObjectProphecy
     */
    private $conflictResolver;

    /**
     * @var DefunctRouteHandlerInterface|ObjectProphecy
     */
    private $defunctRouteHandler;

    public function setUp()
    {
        $this->serviceRegistry = new ServiceRegistry();
        $this->tokenProvider = $this->createMock(TokenProviderInterface::class);
        $this->conflictResolver = $this->createMock(ConflictResolverInterface::class);
        $this->defunctRouteHandler = $this->createMock(DefunctRouteHandlerInterface::class);
    }

    public function testRegistration()
    {
        $tps = ['tp_1', 'tp_2'];
        $crs = ['cr_1', 'cr_2'];
        $defunctRouteHandlers = ['dfrh_1', 'dfrh_2'];

        foreach ($tps as $tp) {
            $this->serviceRegistry->registerTokenProvider($tp, $this->tokenProvider);
        }

        foreach ($crs as $cr) {
            $this->serviceRegistry->registerConflictResolver($cr, $this->conflictResolver);
        }

        foreach ($defunctRouteHandlers as $defunctRouteHandler) {
            $this->serviceRegistry->registerDefunctRouteHandler($defunctRouteHandler, $this->defunctRouteHandler);
        }

        foreach ($tps as $tp) {
            $res = $this->serviceRegistry->getTokenProvider($tp);
            $this->assertSame($this->tokenProvider, $res);
        }

        foreach ($crs as $cr) {
            $res = $this->serviceRegistry->getConflictResolver($cr);
            $this->assertSame($this->conflictResolver, $res);
        }

        foreach ($defunctRouteHandlers as $defunctRouteHandler) {
            $res = $this->serviceRegistry->getDefunctRouteHandler($defunctRouteHandler);
            $this->assertsame($this->defunctRouteHandler, $res);
        }
    }
}
