<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\Adapter;

use Symfony\Cmf\Component\RoutingAuto\Adapter\EventDispatchingAdapter;
use Prophecy\Argument;
use Symfony\Cmf\Component\RoutingAuto\RoutingAutoEvents;

class EventDispatchingAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->realAdapter = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\AdapterInterface');
        $this->dispatcher = $this->prophesize('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->uriContext = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContext');
        $this->autoRoute = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
        $this->autoRoute2 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
        $this->adapter = new EventDispatchingAdapter(
            $this->realAdapter->reveal(),
            $this->dispatcher->reveal()
        );
    }

    public function testDispatchCreate()
    {
        $this->realAdapter->createAutoRoute($this->uriContext->reveal(), null, 'fr')->willReturn($this->autoRoute->reveal());
        $this->dispatcher->dispatch(
            RoutingAutoEvents::POST_CREATE, Argument::type('Symfony\Cmf\Component\RoutingAuto\Event\AutoRouteCreateEvent')
        )->shouldBeCalled();

        $this->adapter->createAutoRoute($this->uriContext->reveal(), null, 'fr');
    }

    public function testDispatchMigrate()
    {
        $this->dispatcher->dispatch(
            RoutingAutoEvents::POST_MIGRATE, Argument::type('Symfony\Cmf\Component\RoutingAuto\Event\AutoRouteMigrateEvent')
        )->shouldBeCalled();

        $this->adapter->migrateAutoRouteChildren(
            $this->autoRoute->reveal(),
            $this->autoRoute2->reveal()
        );
    }
}
