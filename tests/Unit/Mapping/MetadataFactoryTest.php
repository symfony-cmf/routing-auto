<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\Mapping;

use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Cmf\Component\RoutingAuto\Mapping\AutoRouteDefinition;
use Symfony\Cmf\Component\RoutingAuto\Mapping\ClassMetadata;
use Symfony\Cmf\Component\RoutingAuto\Mapping\MetadataFactory;
use Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\ChildClass;
use Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\GrandParentClass;
use Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\Parent1Class;
use Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\ParentClass;

class MetadataFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MetadataFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new MetadataFactory();
    }

    public function testStoreAndGetClassMetadata()
    {
        /** @var ClassMetadata|ObjectProphecy $stdClassMetadata */
        $stdClassMetadata = $this->prophesize(ClassMetadata::class);
        $stdClassMetadata->getClassName()->willReturn('stdClass');
        $stdClassMetadata->getExtendedClass()->willReturn(null);
        $classMetadata = $stdClassMetadata->reveal();

        $this->factory->addMetadatas([$classMetadata]);

        $this->assertSame($classMetadata, $this->factory->getMetadataForClass('stdClass'));
    }

    public function provideTestMerge()
    {
        return [
            [
                [
                    'defunctRouteHandler' => null,
                    'conflictResolver' => null,
                ],
                [
                    'defunctRouteHandler' => null,
                    'conflictResolver' => null,
                ],
                [
                    'defunctRouteHandler' => null,
                    'conflictResolver' => null,
                ],
            ],

            [
                [
                    'defunctRouteHandler' => ['name' => 'defunct1'],
                    'conflictResolver' => ['name' => 'conflict1'],
                ],
                [
                    'defunctRouteHandler' => null,
                    'conflictResolver' => null,
                ],
                [
                    'defunctRouteHandler' => ['name' => 'defunct1'],
                    'conflictResolver' => ['name' => 'conflict1'],
                ],
            ],

            [
                [
                    'defunctRouteHandler' => null,
                    'conflictResolver' => null,
                ],
                [
                    'defunctRouteHandler' => ['name' => 'defunct1'],
                    'conflictResolver' => ['name' => 'conflict1'],
                ],
                [
                    'defunctRouteHandler' => ['name' => 'defunct1'],
                    'conflictResolver' => ['name' => 'conflict1'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideTestMerge
     */
    public function testMerge($parentData, $childData, $expectedData)
    {
        $parentMetadata = new ClassMetadata(ParentClass::class);
        $parentMetadata->setDefunctRouteHandler($parentData['defunctRouteHandler']);
        $parentMetadata->setConflictResolver($parentData['conflictResolver']);

        $childMetadata = new ClassMetadata(ChildClass::class);
        $childMetadata->setDefunctRouteHandler($childData['defunctRouteHandler']);
        $childMetadata->setConflictResolver($childData['conflictResolver']);

        $this->factory->addMetadatas([$childMetadata, $parentMetadata]);

        $resolvedMetadata = $this->factory->getMetadataForClass(ChildClass::class);

        $this->assertSame($expectedData['defunctRouteHandler'], $resolvedMetadata->getDefunctRouteHandler());
        $this->assertSame($expectedData['conflictResolver'], $resolvedMetadata->getConflictResolver());
    }

    public function testMergingParentClasses()
    {
        $childMetadata = new ClassMetadata(ChildClass::class);
        $childMetadata->setAutoRouteDefinition(0, new AutoRouteDefinition('{parent}/{title}'));
        $childTokenProvider = $this->createTokenProvider('provider1');
        $childTokenProviderTitle = $this->createTokenProvider('provider2');
        $childMetadata->addTokenProvider('category', $childTokenProvider);
        $childMetadata->addTokenProvider('title', $childTokenProviderTitle);

        $parentMetadata = new ClassMetadata(ParentClass::class);
        $parentMetadata->setAutoRouteDefinition(0, new AutoRouteDefinition('{parent}/{publish_date}'));
        $parentTokenProvider = $this->createTokenProvider('provider3');
        $parentTokenProviderDate = $this->createTokenProvider('provider4');
        $parentMetadata->addTokenProvider('category', $parentTokenProvider);
        $parentMetadata->addTokenProvider('publish_date', $parentTokenProviderDate);

        $grandParentMetadata = new ClassMetadata(GrandParentClass::class);
        $grandParentMetadata->setAutoRouteDefinition(0, new AutoRouteDefinition('/{category}'));

        $this->factory->addMetadatas([$childMetadata, $parentMetadata, $grandParentMetadata]);

        $resolvedMetadata = $this->factory->getMetadataForClass(ChildClass::class);
        $resolvedProviders = $resolvedMetadata->getTokenProviders();

        $this->assertSame($childTokenProvider, $resolvedProviders['category']);
        $this->assertSame($childTokenProviderTitle, $resolvedProviders['title']);
        $this->assertSame($parentTokenProviderDate, $resolvedProviders['publish_date']);

        $this->assertEquals('/{category}/{publish_date}/{title}', $resolvedMetadata->getAutoRouteDefinition(0)->getUriSchema());
    }

    public function testMergeExtendedClass()
    {
        $parentMetadata = new ClassMetadata(ParentClass::class);
        $parentMetadata->setAutoRouteDefinition('one', new AutoRouteDefinition('{title}'));
        $parentMetadata->setExtendedClass(Parent1Class::class);

        $parent1Metadata = new ClassMetadata(Parent1Class::class);
        $parent1TokenProvider = $this->createTokenProvider('provider1');
        $parent1Metadata->addTokenProvider('title', $parent1TokenProvider);

        $this->factory->addMetadatas([$parentMetadata, $parent1Metadata]);

        $resolvedMetadata = $this->factory->getMetadataForClass(ParentClass::class);
        $resolvedProviders = $resolvedMetadata->getTokenProviders();
        $this->assertSame($parent1TokenProvider, $resolvedProviders['title']);
        $this->assertEquals('{title}', $resolvedMetadata->getAutoRouteDefinition('one')->getUriSchema());
    }

    /**
     * @expectedException \Symfony\Cmf\Component\RoutingAuto\Mapping\Exception\CircularReferenceException
     * @expectedExceptionMessage "Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\ParentClass"
     */
    public function testFailsWithCircularReference()
    {
        $parentMetadata = new ClassMetadata(ParentClass::class);
        $parentMetadata->setAutoRouteDefinition('one', new AutoRouteDefinition('{title}'));
        $parentMetadata->setExtendedClass(Parent1Class::class);

        $parent1Metadata = new ClassMetadata(Parent1Class::class);
        $parent1Metadata->setExtendedClass(ParentClass::class);
        $parent1TokenProvider = $this->createTokenProvider('provider1');
        $parent1Metadata->addTokenProvider('title', $parent1TokenProvider);

        $this->factory->addMetadatas([$parentMetadata, $parent1Metadata]);

        $this->factory->getMetadataForClass(ParentClass::class);
    }

    /**
     * @expectedException \Symfony\Cmf\Component\RoutingAuto\Mapping\Exception\CircularReferenceException
     * @expectedExceptionMessage "Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\ChildClass"
     */
    public function testsFailsWithPhpCircularReference()
    {
        $childMetadata = new ClassMetadata(ChildClass::class);
        $childMetadata->setAutoRouteDefinition('one', new AutoRouteDefinition('{title}'));

        $parentMetadata = new ClassMetadata(ParentClass::class);
        $parentMetadata->setExtendedClass(ChildClass::class);
        $parentMetadata->addTokenProvider('title', $this->createTokenProvider('provider1'));

        $this->factory->addMetadatas([$childMetadata, $parentMetadata]);

        $this->factory->getMetadataForClass(ChildClass::class);
    }

    protected function createTokenProvider($name)
    {
        return ['name' => $name];
    }
}
