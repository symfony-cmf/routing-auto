<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\DefunctRouteHandler;

use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Cmf\Component\RoutingAuto\AdapterInterface;
use Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandler\DelegatingDefunctRouteHandler;
use Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandlerInterface;
use Symfony\Cmf\Component\RoutingAuto\Mapping\ClassMetadata;
use Symfony\Cmf\Component\RoutingAuto\Mapping\MetadataFactory;
use Symfony\Cmf\Component\RoutingAuto\ServiceRegistry;
use Symfony\Cmf\Component\RoutingAuto\UriContextCollection;

class DelegatingDefunctRouteHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MetadataFactory|ObjectProphecy
     */
    private $metadataFactory;

    /**
     * @var AdapterInterface|ObjectProphecy
     */
    private $adapter;

    /**
     * @var ServiceRegistry|ObjectProphecy
     */
    private $serviceRegistry;

    /**
     * @var UriContextCollection|ObjectProphecy
     */
    private $uriContextCollection;

    /**
     * @var ClassMetadata|ObjectProphecy
     */
    private $metadata;

    /**
     * @var DefunctRouteHandlerInterface|ObjectProphecy
     */
    private $delegatedHandler;

    /**
     * @var object
     */
    private $subject;

    /**
     * @var DelegatingDefunctRouteHandler
     */
    private $delegatingDefunctRouteHandler;

    public function setUp()
    {
        $this->metadataFactory = $this->prophesize(MetadataFactory::class);
        $this->adapter = $this->prophesize(AdapterInterface::class);
        $this->serviceRegistry = $this->prophesize(ServiceRegistry::class);
        $this->uriContextCollection = $this->prophesize(UriContextCollection::class);
        $this->metadata = $this->prophesize(ClassMetadata::class);
        $this->delegatedHandler = $this->prophesize(DefunctRouteHandlerInterface::class);

        $this->subject = new \stdClass();

        $this->delegatingDefunctRouteHandler = new DelegatingDefunctRouteHandler(
            $this->metadataFactory->reveal(),
            $this->adapter->reveal(),
            $this->serviceRegistry->reveal()
        );
    }

    public function testHandleDefunctRoutes()
    {
        $this->uriContextCollection->getSubject()->willReturn($this->subject);
        $this->adapter->getRealClassName('stdClass')->willReturn('stdClass');
        $this->metadataFactory->getMetadataForClass('stdClass')->willReturn($this->metadata);
        $this->metadata->getDefunctRouteHandler()->willReturn([
            'name' => 'foobar',
        ]);
        $this->serviceRegistry->getDefunctRouteHandler('foobar')->willReturn($this->delegatedHandler);
        $this->delegatedHandler->handleDefunctRoutes($this->uriContextCollection->reveal())->shouldBeCalled();
        $this->delegatingDefunctRouteHandler->handleDefunctRoutes($this->uriContextCollection->reveal());
    }
}
