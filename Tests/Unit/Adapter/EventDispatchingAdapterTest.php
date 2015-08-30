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
use Symfony\Cmf\Component\RoutingAuto\Event\AutoRouteCreateEvent;
use Symfony\Cmf\Component\RoutingAuto\Event\AutoRouteMigrateEvent;
use Symfony\Cmf\Component\RoutingAuto\Tests\Unit\Adapter\EventDispatchingAdapterSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventDispatchingAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->realAdapter = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\AdapterInterface');
        $this->dispatcher = new EventDispatcher();
        $this->subscriber = new EventDispatchingAdapterSubscriber();
        $this->dispatcher->addSubscriber($this->subscriber);
        $this->uriContext = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContext');
        $this->autoRoute = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
        $this->autoRoute2 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
        $this->adapter = new EventDispatchingAdapter(
            $this->realAdapter->reveal(),
            $this->dispatcher
        );
    }

    public function testDispatchCreate()
    {
        $this->realAdapter->createAutoRoute($this->uriContext->reveal(), null, 'fr')->willReturn($this->autoRoute->reveal());
        $this->adapter->createAutoRoute($this->uriContext->reveal(), null, 'fr');
        $this->assertNotNull($this->subscriber->createEvent);
        $this->assertInstanceOf('Symfony\Cmf\Component\RoutingAuto\Event\AutoRouteCreateEvent', $this->subscriber->createEvent);
        $this->assertSame($this->autoRoute->reveal(), $this->subscriber->createEvent->getAutoRoute());
        $this->assertSame($this->uriContext->reveal(), $this->subscriber->createEvent->getUriContext());
    }

    public function testDispatchMigrate()
    {
        $this->adapter->migrateAutoRouteChildren(
            $this->autoRoute->reveal(),
            $this->autoRoute2->reveal()
        );
        $this->assertNotNull($this->subscriber->migrateEvent);
        $this->assertInstanceOf('Symfony\Cmf\Component\RoutingAuto\Event\AutoRouteMigrateEvent', $this->subscriber->migrateEvent);
        $this->assertSame($this->autoRoute->reveal(), $this->subscriber->migrateEvent->getSrcAutoRoute());
        $this->assertSame($this->autoRoute2->reveal(), $this->subscriber->migrateEvent->getDestAutoRoute());
    }
}

class EventDispatchingAdapterSubscriber implements EventSubscriberInterface
{
    public $createEvent;
    public $migrateEvent;

    public static function getSubscribedEvents()
    {
        return array(
            RoutingAutoEvents::POST_CREATE => 'handleCreate',
            RoutingAutoEvents::POST_MIGRATE => 'handleMigrate',
        );
    }

    public function handleCreate(AutoRouteCreateEvent $createEvent)
    {
        $this->createEvent = $createEvent;
    }

    public function handleMigrate(AutoRouteMigrateEvent $migrateEvent)
    {
        $this->migrateEvent = $migrateEvent;
    }
}
