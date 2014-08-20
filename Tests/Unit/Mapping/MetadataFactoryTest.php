<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\Mapping;

use Symfony\Cmf\Component\RoutingAuto\Mapping\ClassMetadata;
use Symfony\Cmf\Component\RoutingAuto\Mapping\MetadataFactory;
use Symfony\Cmf\Component\RoutingAuto\Tests\Unit\BaseTestCase;
use Prophecy\Argument;

class MetadataFactoryTest extends BaseTestCase
{
    protected $factory;
    protected $driver;

    public function setUp()
    {
        parent::setUp();

        $this->driver = $this->prophet->prophesize('Metadata\Driver\AdvancedDriverInterface');
        $this->factory = new MetadataFactory($this->driver->reveal());
    }

    public function testStoreAndGetClassMetadata()
    {
        $stdClassMetadata = $this->prophet->prophesize('Symfony\Cmf\component\RoutingAuto\Mapping\ClassMetadata');
        $stdClassMetadata->getExtendedClass()->willReturn(null);
        $classMetadata = $stdClassMetadata->reveal();

        $this->driver->loadMetadataForClass(Argument::which('name', 'stdClass'))->willReturn($classMetadata);

        $this->assertSame($classMetadata, $this->factory->getMetadataForClass('stdClass'));
    }

    public function testMergingParentClasses()
    {
        $childMetadata = new ClassMetadata('Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\ChildClass');
        $childMetadata->setUriSchema('{parent}/{title}');
        $childTokenProvider = $this->createTokenProvider('provider1');
        $childTokenProviderTitle = $this->createTokenProvider('provider2');
        $childMetadata->addTokenProvider('category', $childTokenProvider);
        $childMetadata->addTokenProvider('title', $childTokenProviderTitle);

        $parentMetadata = new ClassMetadata('Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\ParentClass');
        $parentMetadata->setUriSchema('/{category}/{publish_date}');
        $parentTokenProvider = $this->createTokenProvider('provider3');
        $parentTokenProviderDate = $this->createTokenProvider('provider4');
        $parentMetadata->addTokenProvider('category', $parentTokenProvider);
        $parentMetadata->addTokenProvider('publish_date', $parentTokenProviderDate);

        $this->driver->loadMetadataForClass(Argument::which('name', 'Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\ChildClass'))->willReturn($childMetadata);
        $this->driver->loadMetadataForClass(Argument::which('name', 'Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\ParentClass'))->willReturn($parentMetadata);

        $resolvedMetadata = $this->factory->getMetadataForClass('Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\ChildClass');
        $resolvedProviders = $resolvedMetadata->getTokenProviders();
        $this->assertSame($childTokenProvider, $resolvedProviders['category']);
        $this->assertSame($childTokenProviderTitle, $resolvedProviders['title']);
        $this->assertSame($parentTokenProviderDate, $resolvedProviders['publish_date']);

        $this->assertEquals('/{category}/{publish_date}/{title}', $resolvedMetadata->getUriSchema());
    }

    public function testMergeExtendedClass()
    {
        $parentMetadata = new ClassMetadata('Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\ParentClass');
        $parentMetadata->setUriSchema('{title}');
        $parentMetadata->setExtendedClass('Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\Parent1Class');

        $parent1Metadata = new ClassMetadata('Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\Parent1Class');
        $parent1TokenProvider = $this->createTokenProvider('provider1');
        $parent1Metadata->addTokenProvider('title', $parent1TokenProvider);

        $this->driver->loadMetadataForClass(Argument::which('name', 'Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\ParentClass'))->willReturn($parentMetadata);
        $this->driver->loadMetadataForClass(Argument::which('name', 'Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\Parent1Class'))->willReturn($parent1Metadata);


        $resolvedMetadata = $this->factory->getMetadataForClass('Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\ParentClass');
        $resolvedProviders = $resolvedMetadata->getTokenProviders();
        $this->assertSame($parent1TokenProvider, $resolvedProviders['title']);
        $this->assertEquals('{title}', $resolvedMetadata->getUriSchema());
    }

    /**
     * @expectedException \LogicException
     */
    public function testFailsWithCircularReference()
    {
        $parentMetadata = new ClassMetadata('Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\ParentClass');
        $parentMetadata->setUriSchema('{title}');
        $parentMetadata->setExtendedClass('Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\Parent1Class');

        $parent1Metadata = new ClassMetadata('Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\Parent1Class');
        $parent1Metadata->setExtendedClass('Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\ParentClass');
        $parent1TokenProvider = $this->createTokenProvider('provider1');
        $parent1Metadata->addTokenProvider('title', $parent1TokenProvider);

        $this->driver->loadMetadataForClass(Argument::which('name', 'Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\ParentClass'))->willReturn($parentMetadata);
        $this->driver->loadMetadataForClass(Argument::which('name', 'Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\Parent1Class'))->willReturn($parent1Metadata);

        $resolvedMetadata = $this->factory->getMetadataForClass('Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\ParentClass');
    }

    public function testCaching()
    {
        $cache = $this->prophet->prophesize('Metadata\Cache\CacheInterface');
        $factory = new MetadataFactory($this->driver->reveal(), $cache->reveal());

        $classMetadata = $this->prophet->prophesize('Symfony\Cmf\Component\RoutingAuto\Mapping\ClassMetadata');
        $classMetadata->isFresh()->willReturn(true);
        $metadata = $classMetadata->reveal();

        $cache->loadClassMetadataFromCache(Argument::which('name', 'stdClass'))->willReturn($metadata)->shouldBeCalled();

        $this->driver->loadMetadataForClass(Argument::any())->shouldNotBeCalled();

        $this->assertEquals($metadata, $factory->getMetadataForClass('stdClass'));
    }

    public function testStoresInCachingWhenNotFresh()
    {
        $cache = $this->prophet->prophesize('Metadata\Cache\CacheInterface');
        $factory = new MetadataFactory($this->driver->reveal(), $cache->reveal());

        $classMetadata = $this->prophet->prophesize('Symfony\Cmf\Component\RoutingAuto\Mapping\ClassMetadata');
        $classMetadata->isFresh()->willReturn(false);

        $loadedClassMetadata = $this->prophet->prophesize('Symfony\Cmf\Component\RoutingAuto\Mapping\ClassMetadata');
        $loadedClassMetadata->isFresh()->willReturn(true);
        $loadedClassMetadata->getExtendedClass()->willReturn(null);
        $metadata = $loadedClassMetadata->reveal();

        $this->driver->loadMetadataForClass(Argument::any())->willReturn($metadata);

        $cache->loadClassMetadataFromCache(Argument::which('name', 'stdClass'))->willReturn($classMetadata->reveal());
        $cache->putClassMetadataInCache($metadata)->shouldBeCalled();

        $factory->getMetadataForClass('stdClass');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetAllClassNamesFailsWhenDriverIsNotAdvanced()
    {
        $driver = $this->prophet->prophesize('Metadata\Driver\DriverInterface');
        $factory = new MetadataFactory($driver->reveal());

        $factory->getAllClassNames();
    }

    public function testGetAllClassNames()
    {
        $this->driver->getAllClassNames()->willReturn(array('stdClass', 'stdClass1', 'stdClass2'));

        $this->assertEquals(array('stdClass', 'stdClass1', 'stdClass2'), $this->factory->getAllClassNames());
    }

    protected function createTokenProvider($name)
    {
        return array('name' => $name);
    }
}
