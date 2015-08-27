<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit;

use Symfony\Cmf\Component\RoutingAuto\AutoRouteManager;
use Symfony\Cmf\Component\RoutingAuto\UriContextCollection;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;

class AutoRouteManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->adapter = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\AdapterInterface');
        $this->uriContextBuilder = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContextBuilder');
        $this->defunctRouteHandler = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandlerInterface');
        $this->manager = new AutoRouteManager(
            $this->adapter->reveal(),
            $this->uriContextBuilder->reveal(),
            $this->defunctRouteHandler->reveal()
        );

        $this->route1Meta = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Mapping\RouteMetadata');
        $this->route1 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
        $this->route2 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
        $this->uriContext1 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContext');
        $this->uriContext1->getUri()->willReturn('/u/r/i/1');
        $this->uriContext2 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContext');
        $this->uriContext2->getUri()->willReturn('/u/r/i/2');
    }

    /**
     * It should build a localized URI Context Collection
     * It should create new routes
     */
    public function testCreateNewRoutes()
    {
        $document = new \stdClass;
        $collection = new UriContextCollection($document);
        $uriContexts = array($this->uriContext1, $this->uriContext2);
        $this->adapter->getLocales($document)->willReturn(array('fr', 'de'));
        $this->adapter->translateObject($document, 'fr')->shouldBeCalled();
        $this->adapter->translateObject($document, 'de')->shouldBeCalled();

        $this->uriContextBuilder->build($collection, 'fr')->will(function ($args) use ($uriContexts) {
            $args[0]->addUriContext($uriContexts[0]->reveal());
        });
        $this->uriContextBuilder->build($collection, 'de')->will(function ($args) use ($uriContexts) {
            $args[0]->addUriContext($uriContexts[1]->reveal());
        });

        $this->adapter->findRouteForUri('/u/r/i/1', $this->uriContext1)->willReturn(null);
        $this->adapter->findRouteForUri('/u/r/i/2', $this->uriContext2)->willReturn(null);
        $this->adapter->generateAutoRouteTag($this->uriContext1)->willReturn('tag1');
        $this->adapter->generateAutoRouteTag($this->uriContext2)->willReturn('tag2');
        $this->adapter->createAutoRoute($this->uriContext1, $document, 'tag1')->willReturn($this->route1->reveal());
        $this->adapter->createAutoRoute($this->uriContext2, $document, 'tag2')->willReturn($this->route2->reveal());
        $this->uriContext1->setAutoRoute($this->route1)->shouldBeCalled();
        $this->uriContext2->setAutoRoute($this->route2)->shouldBeCalled();
        $this->manager->buildUriContextCollection($collection);
    }

    /**
     * It should build a non-localized URI Context Collection
     */
    public function testNonLocalized()
    {
        $document = new \stdClass;
        $collection = new UriContextCollection($document);
        $uriContext = $this->uriContext1;
        $this->adapter->getLocales($document)->willReturn(null);

        $this->uriContextBuilder->build($collection, null)->will(function ($args) use ($uriContext) {
            $args[0]->addUriContext($uriContext->reveal());
        });

        $this->adapter->findRouteForUri('/u/r/i/1', $this->uriContext1)->willReturn(null);
        $this->adapter->generateAutoRouteTag($this->uriContext1)->willReturn('tag1');
        $this->adapter->createAutoRoute($this->uriContext1, $document, 'tag1')->willReturn($this->route1->reveal());
        $this->uriContext1->setAutoRoute($this->route1)->shouldBeCalled();
        $this->manager->buildUriContextCollection($collection);
    }

    /**
     * It should use an existing route for the same content
     * It should set an existing route type to PRIMARY
     */
    public function testExistingSameContent()
    {
        $document = new \stdClass;
        $collection = new UriContextCollection($document);
        $uriContext = $this->uriContext1;
        $this->adapter->getLocales($document)->willReturn(null);

        $this->uriContextBuilder->build($collection, null)->will(function ($args) use ($uriContext) {
            $args[0]->addUriContext($uriContext->reveal());
        });

        $this->adapter->findRouteForUri('/u/r/i/1', $this->uriContext1)->willReturn($this->route1->reveal());
        $this->adapter->compareAutoRouteContent($this->route1->reveal(), $document)->willReturn(true);
        $this->route1->setType(AutoRouteInterface::TYPE_PRIMARY)->shouldBeCalled();

        $this->uriContext1->setAutoRoute($this->route1)->shouldBeCalled();
        $this->manager->buildUriContextCollection($collection);
    }

    /**
     * It should resolve conflicts with existing routes that are not related to the same content.
     */
    public function testExistingNotSameContent()
    {
        $document = new \stdClass;
        $collection = new UriContextCollection($document);
        $uriContext = $this->uriContext1;
        $this->adapter->getLocales($document)->willReturn(null);

        $this->uriContextBuilder->build($collection, null)->will(function ($args) use ($uriContext) {
            $args[0]->addUriContext($uriContext->reveal());
        });

        $this->adapter->findRouteForUri('/u/r/i/1', $this->uriContext1)->willReturn($this->route1->reveal());
        $this->adapter->compareAutoRouteContent($this->route1->reveal(), $document)->willReturn(false);
        $this->uriContext1->getRouteMetadata()->willReturn($this->route1Meta->reveal());

        $this->uriContext1->setAutoRoute($this->route1)->shouldBeCalled();
        $this->manager->buildUriContextCollection($collection);
    }
}
