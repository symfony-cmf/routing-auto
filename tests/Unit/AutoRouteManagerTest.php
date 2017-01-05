<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit;

use Symfony\Cmf\Component\RoutingAuto\AutoRouteManager;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;

class AutoRouteManagerTest extends \PHPUnit_Framework_TestCase
{
    private $adapter;
    private $uriGenerator;
    private $defunctRouteHandler;
    private $collectionBuilder;

    public function setUp()
    {
        $this->adapter = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\AdapterInterface');
        $this->uriGenerator = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriGeneratorInterface');
        $this->defunctRouteHandler = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandlerInterface');
        $this->collectionBuilder = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContextCollectionBuilder');

        $this->manager = new AutoRouteManager(
            $this->adapter->reveal(),
            $this->uriGenerator->reveal(),
            $this->defunctRouteHandler->reveal(),
            $this->collectionBuilder->reveal()
        );

        $this->collection = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContextCollection');
        $this->context1 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContext');
        $this->context2 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContext');
        $this->autoRoute1 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
        $this->autoRoute2 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');

        $this->subject = new \stdClass();
    }

    /**
     * It should delegate the generation of the collection and resolve the URIs
     * in it (for non-existing URIs).
     * It should handle defunct routes.
     */
    public function testBuildCollection()
    {
        $this->prepareBuildCollection();

        $this->context1->getLocale()->willReturn(null);
        $this->context2->getLocale()->willReturn(null);

        $this->manager->buildUriContextCollection($this->collection->reveal());

        // test handle defunt routes:
        // would like to make this a separate test with @depends
        // but PHPUnit does not allow it.
        $this->defunctRouteHandler->handleDefunctRoutes($this->collection->reveal())->shouldBeCalled();
        $this->manager->handleDefunctRoutes();
    }

    /**
     * It should translate subject objects.
     */
    public function testBuildCollectionTranslate()
    {
        $this->prepareBuildCollection();
        $translatedSubject = new \stdClass();

        $this->context1->getLocale()->willReturn('fr');
        $this->context2->getLocale()->willReturn('de');
        $this->adapter->translateObject($this->subject, 'fr')->willReturn($translatedSubject)->shouldBeCalled();
        $this->adapter->translateObject($this->subject, 'de')->willReturn($this->subject)->shouldBeCalled();
        $this->context1->setTranslatedSubject($this->subject)->shouldBeCalled();

        $this->manager->buildUriContextCollection($this->collection->reveal());
    }

    private function prepareBuildCollection()
    {
        $this->collectionBuilder->build($this->collection->reveal());
        $this->collection->getUriContexts()->willReturn(array(
            $this->context1->reveal(),
            $this->context2->reveal(),
        ));
        $this->collection->getSubject()->willReturn($this->subject);

        for ($index = 1; $index <= 2; ++$index) {
            $contextVar = 'context'.$index;
            $uri = '/uri'.$index;
            $autoRouteVar = 'autoRoute'.$index;

            $this->uriGenerator->generateUri($this->{$contextVar}->reveal())->willReturn($uri);
            $this->{$contextVar}->getSubject()->willReturn($this->subject);
            $this->{$contextVar}->setUri($uri)->shouldBeCalled();

            $this->adapter->findRouteForUri($uri, $this->{$contextVar})->willReturn(null);
            $this->adapter->generateAutoRouteTag($this->{$contextVar}->reveal())->willReturn('fr');
            $this->adapter->createAutoRoute($this->{$contextVar}, $this->subject, 'fr')->willReturn($this->{$autoRouteVar}->reveal());
            $this->{$contextVar}->setAutoRoute($this->{$autoRouteVar}->reveal())->shouldBeCalled();
        }
    }

    /**
     * It should handle existing URIs.
     *
     * @dataProvider provideBuildCollectionExisting
     */
    public function testBuildCollectionExisting($sameContent)
    {
        $uri = '/uri/to';
        $resolvedUri = '/resolved/uri';

        $this->collectionBuilder->build($this->collection->reveal());
        $this->collection->getUriContexts()->willReturn(array(
            $this->context1->reveal(),
        ));
        $this->collection->getSubject()->willReturn($this->subject);
        $this->uriGenerator->generateUri($this->context1->reveal())->willReturn($uri);
        $this->context1->setUri($uri)->shouldBeCalled();
        $this->context1->getLocale()->willReturn(null);
        $this->context1->getSubject()->willReturn($this->subject);
        $this->adapter->findRouteForUri($uri, $this->context1)->willReturn(
            $this->autoRoute1->reveal()
        );

        // handle existing route
        $this->adapter->compareAutoRouteContent(
            $this->autoRoute1->reveal(),
            $this->subject
        )->willReturn($sameContent);

        $this->context1->getSubject()->willReturn($this->subject);

        if ($sameContent) {
            $this->autoRoute1->setType(AutoRouteInterface::TYPE_PRIMARY)
                ->shouldBeCalled();
        } else {
            $this->uriGenerator->resolveConflict($this->context1->reveal())
                ->willReturn($resolvedUri);
            $this->context1->setUri($resolvedUri)->shouldBeCalled();
            $this->adapter->generateAutoRouteTag($this->context1->reveal())->willReturn('fr');
            $this->adapter->createAutoRoute($this->context1, $this->subject, 'fr')->willReturn($this->autoRoute1->reveal());
        }

        $this->context1->setAutoRoute($this->autoRoute1->reveal())->shouldBeCalled();

        $this->manager->buildUriContextCollection($this->collection->reveal());
    }

    public function provideBuildCollectionExisting()
    {
        return array(
            array(true),
            array(false),
        );
    }
}
