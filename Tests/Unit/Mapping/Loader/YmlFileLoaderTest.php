<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\Mapping\Loader;

use Symfony\Cmf\Component\RoutingAuto\Mapping\Loader\YmlFileLoader;

class YmlFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected $locator;
    protected $loader;

    public function setUp()
    {
        $this->locator = $this->prophesize('Symfony\Component\Config\FileLocatorInterface');
        $this->loader = new YmlFileLoader($this->locator->reveal());
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
        return array(
            array('foo.yml'),
            array('foo.xml', null, false),
            array('foo.yml', 'yaml'),
            array('foo.yml', 'xml', false),
        );
    }

    public function testDoesNothingIfFileIsEmpty()
    {
        $this->locator->locate('empty.yml')->willReturn($this->getFixturesPath('empty.yml'));

        $this->assertEmpty($this->loader->load('empty.yml'));
    }

    /**
     * TODO: This test is rather opaque - we do not know what we are testing. It should be
     * broken up into targeted tests.
     *
     * @dataProvider getFailsOnInvalidConfigFilesData
     */
    public function testFailsOnInvalidConfigFiles($file, $expectedMessage)
    {
        if ($expectedMessage) {
            $this->setExpectedException('InvalidArgumentException', $expectedMessage);
        } else {
            $this->setExpectedException('InvalidArgumentException');
        }
        $this->locator->locate($file)->willReturn($this->getFixturesPath($file));

        $this->loader->load($file);
    }

    public function getFailsOnInvalidConfigFilesData()
    {
        return array(
            array(
                'invalid1.yml',
                null,
            ),
            array(
                'invalid2.yml',
                null,
            ),
            array(
                'invalid3.yml',
                null,
            ),
            array(
                'invalid4.yml',
                null,
            ),
            array(
                'invalid5.yml',
                'Mapping node for "stdClass" must define a list of auto route definitions',
            ),
            array(
                'invalid6.yml',
                'Invalid keys "foo_bar", "bar_foo"',
            ),
        );
    }

    /**
     * @dataProvider getCorrectlyParsesValidConfigFilesData
     */
    public function testCorrectlyParsesValidConfigFiles($file, $check)
    {
        $this->locator->locate($file)->willReturn($this->getFixturesPath($file));

        $result = $this->loader->load($file);

        $this->assertContainsOnlyInstancesOf('Symfony\Cmf\Component\RoutingAuto\Mapping\ClassMetadata', $result);
        $check($result);
    }

    public function getCorrectlyParsesValidConfigFilesData()
    {
        $test = $this;
        $serviceConfig = function ($name, $options = array()) {
            return array('name' => $name, 'options' => $options);
        };

        return array(
            array('valid1.yml', function ($metadatas) use ($test) {
                $test->assertCount(1, $metadatas);
                $metadata = $metadatas[0];
                $test->assertEquals('stdClass', $metadata->getClassName());
                $test->assertCount(0, $metadata->getTokenProviders());

                $definitions = $metadata->getAutoRouteDefinitions();
                $test->assertEquals('/cmf/blog', $metadata->getAutoRouteDefinition('_default')->getUriSchema());
            }),
            array('valid2.yml', function ($metadatas) use ($test, $serviceConfig) {
                $test->assertCount(1, $metadatas);
                $metadata = $metadatas[0];
                $test->assertEquals('stdClass', $metadata->getClassName());

                $definitions = $metadata->getAutoRouteDefinitions();
                $test->assertCount(1, $definitions);
                $test->assertEquals('/forum/{category}/{post_name}', $definitions['_default']->getUriSchema());

                $test->assertCount(2, $metadata->getTokenProviders());
                $units = $metadata->getTokenProviders();

                $test->assertArrayHasKey('category', $units);
                $test->assertEquals($serviceConfig('method', array('method' => 'getCategoryName')), $units['category']);

                $test->assertArrayHasKey('post_name', $units);
                $test->assertEquals($serviceConfig('method', array('method' => 'getName')), $units['post_name']);
            }),
            array('valid3.yml', function ($metadatas) use ($test) {
                $test->assertCount(2, $metadatas);
                $test->assertEquals('stdClass', $metadatas[0]->getClassName());
                $test->assertEquals('/forum/{category}/{post_name}', $metadatas[0]->getAutoRouteDefinition('_default')->getUriSchema());

                $test->assertEquals('Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\ParentClass', $metadatas[1]->getClassName());
                $test->assertEquals('/forum/{category}', $metadatas[1]->getAutoRouteDefinition('_default')->getUriSchema());
                $test->assertEquals('stdClass', $metadatas[1]->getExtendedClass());
            }),
            array('valid4.yml', function ($metadatas) use ($test, $serviceConfig) {
                $test->assertCount(1, $metadatas);
                $metadata = $metadatas[0];

                $test->assertEquals('stdClass', $metadata->getClassName());
                $test->assertEquals('/cmf/blog', $metadata->getAutoRouteDefinition('_default')->getUriSchema());
                $test->assertEquals($serviceConfig('auto_increment'), $metadata->getConflictResolver());
                $test->assertEquals($serviceConfig('leave_redirect'), $metadata->getDefunctRouteHandler());
            }),
            array('valid5.yml', function ($metadatas) use ($test, $serviceConfig) {
                $test->assertCount(1, $metadatas);
                $metadata = $metadatas[0];

                $test->assertEquals('stdClass', $metadata->getClassName());
                $test->assertEquals('/blog/{category}/{slug}', $metadata->getAutoRouteDefinition('_default')->getUriSchema());
                $test->assertEquals($serviceConfig('auto_increment', array('token' => 'category')), $metadata->getConflictResolver());

                $test->assertCount(2, $metadata->getTokenProviders());
                $providers = $metadata->getTokenProviders();
                $test->assertArrayHasKey('category', $providers);
                $test->assertEquals($serviceConfig('method', array('method' => 'getCategoryName')), $providers['category']);
                $test->assertArrayHasKey('slug', $providers);
                $test->assertEquals($serviceConfig('property', array('property' => 'title', 'slugify' => true)), $providers['slug']);
            }),
            array('valid6.yml', function ($metadatas) use ($test, $serviceConfig) {
                $test->assertCount(1, $metadatas);
                $metadata = $metadatas[0];
                $test->assertEquals('stdClass', $metadata->getClassName());
                $test->assertEquals('/forum/{category}/{post_title}', $metadata->getAutoRouteDefinition('_default')->getUriSchema());
                $providers = $metadata->getTokenProviders();
                $test->assertArrayHasKey('category', $providers);
                $test->assertEquals('foo', $providers['category']['name']);
            }),
            array('valid7.yml', function ($metadatas) use ($test, $serviceConfig) {
                $test->assertCount(1, $metadatas);
                $metadata = $metadatas[0];
                $test->assertEquals('stdClass', $metadata->getClassName());

                $test->assertEquals('/forum/{category}/{post_title}/edit', $metadata->getAutoRouteDefinition(0)->getUriSchema());
                $test->assertEquals(
                    array(
                        '_type' => 'edit',
                    ), $metadata->getAutoRouteDefinition(0)->getDefaults()
                );

                $test->assertEquals('/forum/{category}/{post_title}/view', $metadata->getAutoRouteDefinition(1)->getUriSchema());
                $test->assertEquals(
                    array(
                        '_type' => 'view',
                    ), $metadata->getAutoRouteDefinition(1)->getDefaults()
                );

                $providers = $metadata->getTokenProviders();
                $test->assertArrayHasKey('category', $providers);
                $test->assertEquals('foo', $providers['category']['name']);
            }),
        );
    }

    protected function getFixturesPath($fixture)
    {
        return __DIR__.'/../../../Resources/Fixtures/loader_config/'.$fixture;
    }
}
