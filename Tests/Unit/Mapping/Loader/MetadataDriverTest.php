<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\Mapping\Loader;

use Symfony\Cmf\Component\RoutingAuto\Mapping\Loader\MetadataDriver;
use Symfony\Cmf\Component\RoutingAuto\Tests\Unit\BaseTestCase;
use Prophecy\Argument;

class MetadataDriverTest extends BaseTestCase
{
    protected $loader;
    protected $driver;

    public function setUp()
    {
        parent::setUp();

        $this->loader = $this->prophet->prophesize('Symfony\Component\Config\Loader\LoaderInterface');
        $this->driver = new MetadataDriver($this->loader->reveal(), array(array('path' => 'some_resource.yml')));
    }

    public function testSingletonLoading()
    {
        $this->loader->load('some_resource.yml', null)
            ->shouldBeCalledTimes(1)
            ->willReturn(array($this->getMetadata()))
        ;

        $this->driver->loadMetadataForClass(new \ReflectionClass('stdClass'));
        $this->driver->loadMetadataForClass(new \ReflectionClass('stdClass'));
    }

    public function testClassMerging()
    {
        $metadata = $this->getMetadata();
        $metadata->merge(Argument::type('Symfony\Cmf\Component\RoutingAuto\Mapping\ClassMetadata'))
            ->shouldBeCalled()
        ;

        $metadata1 = $this->getMetadata();

        $this->loader->load('some_resource.yml', null)->willReturn(array(
            $metadata->reveal(),
            $metadata1->reveal()
        ));

        $this->driver->loadMetadataForClass(new \ReflectionClass('stdClass'));
    }

    public function testDoesNothingIfClassHasNoMetadata()
    {
        $this->loader->load('some_resource.yml', null)->willReturn(array());

        $this->assertNull($this->driver->loadMetadataForClass(new \ReflectionClass('stdClass')));
    }

    public function testCanGetAllDefinedClasses()
    {
        $this->loader->load('class1.yml', null)->willReturn(array(
            $this->getMetadata()->reveal(),
        ));

        $this->loader->load('class2.yml', null)->willReturn(array(
            $this->getMetadata('DateTime')->reveal(),
        ));

        $driver = new MetadataDriver($this->loader->reveal(), array(
            array('path' => 'class1.yml'),
            array('path' => 'class2.yml'),
        ));

        $this->assertEquals(array('stdClass', 'DateTime'), $driver->getAllClassNames());
    }

    protected function getMetadata($class = 'stdClass')
    {
        $metadata = $this->prophet->prophesize('Symfony\Cmf\Component\RoutingAuto\Mapping\ClassMetadata');
        $metadata->getClassName()->willReturn($class);

        return $metadata;
    }
}
