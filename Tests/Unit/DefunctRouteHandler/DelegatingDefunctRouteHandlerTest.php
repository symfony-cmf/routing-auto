<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\DefunctRouteHandler;

use Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandler\DelegatingDefunctRouteHandler;

class DelegatingDefunctRouteHandlerTest extends \PHPUnit_Framework_TestCase
{
    protected $metadataFactory;
    protected $adapter;
    protected $serviceRegistry;
    protected $uriContextCollection;
    protected $metadata;

    public function setUp()
    {
        $this->metadataFactory = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Mapping\MetadataFactory');
        $this->adapter = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\AdapterInterface');
        $this->serviceRegistry = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\ServiceRegistry');
        $this->uriContextCollection = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContextCollection');
        $this->metadata = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Mapping\ClassMetadata');
        $this->delegatedHandler = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandlerInterface');

        $this->subjectObject = new \stdClass();

        $this->delegatingDefunctRouteHandler = new DelegatingDefunctRouteHandler(
            $this->metadataFactory->reveal(),
            $this->adapter->reveal(),
            $this->serviceRegistry->reveal(),
            $this->uriContextCollection->reveal()
        );
    }

    public function testHandleDefunctRoutes()
    {
        $this->uriContextCollection->getSubjectObject()->willReturn($this->subjectObject);
        $this->adapter->getRealClassName('stdClass')->willReturn('stdClass');
        $this->metadataFactory->getMetadataForClass('stdClass')->willReturn($this->metadata);
        $this->metadata->getDefunctRouteHandler()->willReturn(array(
            'name' => 'foobar',
        ));
        $this->serviceRegistry->getDefunctRouteHandler('foobar')->willReturn($this->delegatedHandler);
        $this->delegatedHandler->handleDefunctRoutes($this->uriContextCollection->reveal())->shouldBeCalled();
        $this->delegatingDefunctRouteHandler->handleDefunctRoutes($this->uriContextCollection->reveal());
    }
}
