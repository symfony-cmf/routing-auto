<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\Adapter;

use Symfony\Cmf\Component\RoutingAuto\Adapter\EventDispatchingAdapter;
use Symfony\Cmf\Component\RoutingAuto\RoutingAutoEvents;
use Symfony\Cmf\Component\RoutingAuto\Event\AutoRouteCreateEvent;
use Symfony\Cmf\Component\RoutingAuto\Event\AutoRouteMigrateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventDispatchingAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->realAdapter = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\AdapterInterface');
        $this->dispatcher = new EventDispatcher();
        $this->adapter = new EventDispatchingAdapter(
            $this->realAdapter->reveal(),
            $this->dispatcher
        );

        $this->subscriber = new EventDispatchingAdapterSubscriber();
        $this->dispatcher->addSubscriber($this->subscriber);
        $this->uriContext = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContext');
        $this->autoRoute = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
        $this->autoRoute2 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
        $this->content = new \stdClass();
    }

    public function testCreateAutoRoute()
    {
        $this->realAdapter->createAutoRoute('/u/r/i', null, 'fr')->willReturn($this->autoRoute->reveal());

        $this->adapter->createAutoRoute('/u/r/i', null, 'fr');

        $this->assertNotNull($this->subscriber->createEvent);
        $this->assertInstanceOf('Symfony\Cmf\Component\RoutingAuto\Event\AutoRouteCreateEvent', $this->subscriber->createEvent);
        $this->assertSame($this->autoRoute->reveal(), $this->subscriber->createEvent->getAutoRoute());
        $this->assertEquals('/u/r/i', $this->subscriber->createEvent->getPath());
    }

    public function testMigrateAutoRouteChildren()
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

    public function testGetLocales()
    {
        $this->realAdapter->getLocales($this->content)->shouldBeCalled()->willReturn(array('de', 'de_at'));
        $locales = $this->adapter->getLocales($this->content);
        $this->assertEquals(array('de', 'de_at'), $locales);
    }

    public function testTranslateObject()
    {
        $translatedContent = new \stdClass();
        $this->realAdapter->translateObject($this->content, 'en')->shouldBeCalled()->willReturn($translatedContent);
        $content = $this->adapter->translateObject($this->content, 'en');
        $this->assertEquals($translatedContent, $content);
    }

    public function testGenerateAutoRouteTag()
    {
        $this->realAdapter->generateAutoRouteTag($this->uriContext->reveal())->willReturn('hello');
        $tag = $this->adapter->generateAutoRouteTag($this->uriContext->reveal());
        $this->assertEquals('hello', $tag);
    }

    public function testRemoveAutoRoute()
    {
        $this->realAdapter->removeAutoRoute($this->autoRoute->reveal())->shouldBeCalled();
        $this->adapter->removeAutoRoute($this->autoRoute->reveal());
    }

    public function testCreateRedirectRoute()
    {
        $this->realAdapter->createRedirectRoute(
            $this->autoRoute->reveal(),
            $this->autoRoute2->reveal()
        )->shouldBeCalled();

        $this->adapter->createRedirectRoute(
            $this->autoRoute->reveal(),
            $this->autoRoute2->reveal()
        );
    }

    public function testGetRealClassName()
    {
        $this->realAdapter->getRealClassName('foo')->willReturn('bar');

        $className = $this->adapter->getRealClassName('foo');
        $this->assertEquals('bar', $className);
    }

    public function testCompareAutoRouteContent()
    {
        $this->realAdapter->compareAutoRouteContent(
            $this->autoRoute->reveal(),
            $this->content
        )->willReturn(false);

        $this->assertFalse(
            $this->adapter->compareAutoRouteContent(
                $this->autoRoute->reveal(),
                $this->content
            )
        );
    }

    public function testGetReferringAutoRoutes()
    {
        $referrers = array($this->autoRoute->reveal(), $this->autoRoute2->reveal());
        $this->realAdapter->getReferringAutoRoutes(
            $this->content
        )->willReturn($referrers);

        $this->assertEquals($referrers, $this->adapter->getReferringAutoRoutes($this->content));
    }

    public function testFindRouteForUri()
    {
        $routes = array($this->autoRoute->reveal(), $this->autoRoute2->reveal());

        $this->realAdapter->findRouteForUri('uri')->willReturn($routes);

        $this->assertEquals($routes, $this->adapter->findRouteForUri('uri'));
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
