<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\Mapping\Loader;

use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Cmf\Component\RoutingAuto\Mapping\ClassMetadata;
use Symfony\Cmf\Component\RoutingAuto\Mapping\Loader\XmlFileLoader;
use Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\ParentClass;
use Symfony\Component\Config\FileLocatorInterface;

class XmlFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileLocatorInterface|ObjectProphecy
     */
    private $locator;

    /**
     * @var XmlFileLoader
     */
    private $loader;

    public function setUp()
    {
        $this->locator = $this->prophesize(FileLocatorInterface::class);
        $this->loader = new XmlFileLoader($this->locator->reveal());
    }

    /**
     * @dataProvider getSupportsData
     */
    public function testSupports($file, $type = null, $support = true)
    {
        $result = $this->loader->supports($file, $type);

        if ($support) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }

    public function getSupportsData()
    {
        return [
            ['foo.xml'],
            ['foo.yml', null, false],

            ['foo.xml', 'xml'],
            ['foo.xml', 'yaml', false],

            ['foo.bar', null, false],
            ['foo.bar', 'yaml', false],
            ['foo.bar', 'xml', false],

            ['foo', null, false],
            ['foo', 'yaml', false],
            ['foo', 'xml', false],

            ['foo.yml', 'bar', false],
            ['foo.xml', 'bar', false],
            ['foo.bar', 'bar', false],
        ];
    }

    public function testDoesNothingIfFileIsEmpty()
    {
        $this->locator->locate('empty.yml')->willReturn($this->getFixturesPath('empty.yml'));

        $this->assertNull($this->loader->load('empty.yml'));
    }

    /**
     * @expectedException \InvalidArgumentException
     *
     * @dataProvider getFailsOnInvalidConfigFilesData
     */
    public function testFailsOnInvalidConfigFiles($file)
    {
        $this->locator->locate($file)->willReturn($this->getFixturesPath($file));

        $this->loader->load($file);
    }

    public function getFailsOnInvalidConfigFilesData()
    {
        $files = [
            'invalid1.xml',
        ];

        return array_map(function ($file) {
            return [$file];
        }, $files);
    }

    /**
     * @dataProvider getCorrectlyParsesValidConfigFilesData
     */
    public function testCorrectlyParsesValidConfigFiles($file, $check)
    {
        $this->locator->locate($file)->willReturn($this->getFixturesPath($file));

        $result = $this->loader->load($file);

        $this->assertContainsOnlyInstancesOf(ClassMetadata::class, $result);
        $check($result);
    }

    public function getCorrectlyParsesValidConfigFilesData()
    {
        $test = $this;
        $serviceConfig = function ($name, $options = []) {
            return ['name' => $name, 'options' => $options];
        };

        return [
            ['valid1.xml', function ($metadatas) use ($test) {
                $test->assertCount(1, $metadatas);
                $metadata = $metadatas[0];
                $test->assertEquals('stdClass', $metadata->getClassName());
                $test->assertEquals('/cmf/blog', $metadata->getAutoRouteDefinition('_default')->getUriSchema());
                $test->assertCount(0, $metadata->getTokenProviders());
            }],
            ['valid2.xml', function ($metadatas) use ($test, $serviceConfig) {
                $test->assertCount(1, $metadatas);
                $metadata = $metadatas[0];
                $test->assertEquals('stdClass', $metadata->getClassName());
                $test->assertEquals('/forum/{category}/{post_name}', $metadata->getAutoRouteDefinition('_default')->getUriSchema());

                $test->assertCount(2, $metadata->getTokenProviders());
                $units = $metadata->getTokenProviders();

                $test->assertArrayHasKey('category', $units);
                $test->assertEquals($serviceConfig('method', ['method' => 'getCategoryName']), $units['category']);

                $test->assertArrayHasKey('post_name', $units);
                $test->assertEquals($serviceConfig('method', ['method' => 'getName']), $units['post_name']);
            }],
            ['valid3.xml', function ($metadatas) use ($test) {
                $test->assertCount(2, $metadatas);
                $test->assertEquals('stdClass', $metadatas[0]->getClassName());
                $test->assertEquals('/forum/{category}/{post_name}', $metadatas[0]->getAutoRouteDefinition('_default')->getUriSchema());

                $test->assertEquals(ParentClass::class, $metadatas[1]->getClassName());
                $test->assertEquals('/forum/{category}', $metadatas[1]->getAutoRouteDefinition('_default')->getUriSchema());
                $test->assertEquals('stdClass', $metadatas[1]->getExtendedClass());
            }],
            ['valid4.xml', function ($metadatas) use ($test, $serviceConfig) {
                $test->assertCount(1, $metadatas);
                $metadata = $metadatas[0];

                $test->assertEquals('stdClass', $metadata->getClassName());
                $test->assertEquals('/cmf/blog', $metadata->getAutoRouteDefinition('_default')->getUriSchema());
                $test->assertEquals($serviceConfig('auto_increment'), $metadata->getConflictResolver());
                $test->assertEquals($serviceConfig('leave_redirect'), $metadata->getDefunctRouteHandler());
            }],
            ['valid5.xml', function ($metadatas) use ($test, $serviceConfig) {
                $test->assertCount(1, $metadatas);
                $metadata = $metadatas[0];

                $test->assertEquals('stdClass', $metadata->getClassName());
                $test->assertEquals('/blog/{category}/{slug}', $metadata->getAutoRouteDefinition('_default')->getUriSchema());
                $test->assertEquals($serviceConfig('auto_increment', ['token' => 'category']), $metadata->getConflictResolver());

                $test->assertCount(2, $metadata->getTokenProviders());
                $providers = $metadata->getTokenProviders();
                $test->assertArrayHasKey('category', $providers);
                $test->assertEquals($serviceConfig('method', ['method' => 'getCategoryName']), $providers['category']);
                $test->assertArrayHasKey('slug', $providers);
                $test->assertEquals($serviceConfig('property', ['property' => 'title', 'slugify' => true]), $providers['slug']);
            }],
            ['valid7.xml', function ($metadatas) use ($test, $serviceConfig) {
                $test->assertCount(1, $metadatas);
                $metadata = $metadatas[0];
                $test->assertEquals('stdClass', $metadata->getClassName());

                $test->assertEquals('/forum/{category}/{post_title}/edit', $metadata->getAutoRouteDefinition('edit')->getUriSchema());
                $test->assertEquals(
                    [
                        '_type' => 'edit',
                    ], $metadata->getAutoRouteDefinition('edit')->getDefaults()
                );

                $test->assertEquals('/forum/{category}/{post_title}/view', $metadata->getAutoRouteDefinition('view')->getUriSchema());
                $test->assertEquals(
                    [
                        '_type' => 'view',
                    ], $metadata->getAutoRouteDefinition('view')->getDefaults()
                );

                $providers = $metadata->getTokenProviders();
                $test->assertArrayHasKey('category', $providers);
                $test->assertEquals('method', $providers['category']['name']);
            }],
        ];
    }

    protected function getFixturesPath($fixture)
    {
        return __DIR__.'/../../../Resources/Fixtures/loader_config/'.$fixture;
    }
}
