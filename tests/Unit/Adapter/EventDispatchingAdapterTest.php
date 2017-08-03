<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\Adapter;

use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Cmf\Component\RoutingAuto\Adapter\EventDispatchingAdapter;
use Symfony\Cmf\Component\RoutingAuto\AdapterInterface;
use Symfony\Cmf\Component\RoutingAuto\Event\AutoRouteCreateEvent;
use Symfony\Cmf\Component\RoutingAuto\Event\AutoRouteMigrateEvent;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Cmf\Component\RoutingAuto\RoutingAutoEvents;
use Symfony\Cmf\Component\RoutingAuto\UriContext;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventDispatchingAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AdapterInterface|ObjectProphecy
     */
    private $realAdapter;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @var EventDispatchingAdapter
     */
    private $adapter;

    /**
     * @var EventDispatchingAdapterSubscriber
     */
    private $subscriber;

    /**
     * @var UriContext|ObjectProphecy
     */
    private $uriContext;

    /**
     * @var AutoRouteInterface|ObjectProphecy
     */
    private $autoRoute;

    /**
     * @var AutoRouteInterface|ObjectProphecy
     */
    private $autoRoute2;

    private $content;

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
        $this->realAdapter->createAutoRoute($this->uriContext->reveal(), 'fr')->willReturn($this->autoRoute->reveal());
        $this->adapter->createAutoRoute($this->uriContext->reveal(), 'fr');
        $this->assertNotNull($this->subscriber->createEvent);
        $this->assertInstanceOf('Symfony\Cmf\Component\RoutingAuto\Event\AutoRouteCreateEvent', $this->subscriber->createEvent);
        $this->assertSame($this->autoRoute->reveal(), $this->subscriber->createEvent->getAutoRoute());
        $this->assertSame($this->uriContext->reveal(), $this->subscriber->createEvent->getUriContext());
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
        $this->realAdapter->getLocales($this->content)->shouldBeCalled()->willReturn(['de', 'de_at']);
        $locales = $this->adapter->getLocales($this->content);
        $this->assertEquals(['de', 'de_at'], $locales);
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

    public function testCompareAutoRouteLocale()
    {
        $locale = 'locale';

        $this->realAdapter->compareAutoRouteLocale(
            $this->autoRoute->reveal(),
            $locale
        )->willReturn(true);

        $this->assertTrue(
            $this->adapter->compareAutoRouteLocale(
                $this->autoRoute->reveal(),
                $locale
            )
        );
    }

    public function testGetReferringAutoRoutes()
    {
        $referrers = [$this->autoRoute->reveal(), $this->autoRoute2->reveal()];
        $this->realAdapter->getReferringAutoRoutes(
            $this->content
        )->willReturn($referrers);

        $this->assertEquals($referrers, $this->adapter->getReferringAutoRoutes($this->content));
    }

    public function testFindRouteForUri()
    {
        $routes = [$this->autoRoute->reveal(), $this->autoRoute2->reveal()];

        $this->realAdapter->findRouteForUri('uri', $this->uriContext->reveal())->willReturn($routes);

        $this->assertEquals($routes, $this->adapter->findRouteForUri('uri', $this->uriContext->reveal()));
    }
}

class EventDispatchingAdapterSubscriber implements EventSubscriberInterface
{
    public $createEvent;
    public $migrateEvent;

    public static function getSubscribedEvents()
    {
        return [
            RoutingAutoEvents::POST_CREATE => 'handleCreate',
            RoutingAutoEvents::POST_MIGRATE => 'handleMigrate',
        ];
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
