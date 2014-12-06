<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\Mapping\Loader;

use Symfony\Cmf\Component\RoutingAuto\Mapping\Loader\XmlFileLoader;
use Symfony\Cmf\Component\RoutingAuto\Tests\Unit\BaseTestCase;

class XmlFileLoaderTest extends BaseTestCase
{
    protected $locator;
    protected $loader;

    public function setUp()
    {
        parent::setUp();

        $this->locator = $this->prophet->prophesize('Symfony\Component\Config\FileLocatorInterface');
        $this->loader  = new XmlFileLoader($this->locator->reveal());
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
            array('foo.xml'),
            array('foo.yml', null, false),
            array('foo.xml', 'xml'),
            array('foo.xml', 'yaml', false),
        );
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
        $files = array(
            'invalid1.xml',
        );

        return array_map(function ($file) {
            return array($file);
        }, $files);
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
            array('valid1.xml', function ($metadatas) use ($test) {
                $test->assertCount(1, $metadatas);
                $metadata = $metadatas[0];
                $test->assertEquals('stdClass', $metadata->getClassName());
                $test->assertEquals('/cmf/blog', $metadata->getUriSchema());
                $test->assertCount(0, $metadata->getTokenProviders());
            }),
            array('valid2.xml', function ($metadatas) use ($test, $serviceConfig) {
                $test->assertCount(1, $metadatas);
                $metadata = $metadatas[0];
                $test->assertEquals('stdClass', $metadata->getClassName());
                $test->assertEquals('/forum/{category}/{post_name}', $metadata->getUriSchema());

                $test->assertCount(2, $metadata->getTokenProviders());
                $units = $metadata->getTokenProviders();

                $test->assertArrayHasKey('category', $units);
                $test->assertEquals($serviceConfig('method', array('method' => 'getCategoryName')), $units['category']);

                $test->assertArrayHasKey('post_name', $units);
                $test->assertEquals($serviceConfig('method', array('method' => 'getName')), $units['post_name']);
            }),
            array('valid3.xml', function ($metadatas) use ($test) {
                $test->assertCount(2, $metadatas);
                $test->assertEquals('stdClass', $metadatas[0]->getClassName());
                $test->assertEquals('/forum/{category}/{post_name}', $metadatas[0]->getUriSchema());

                $test->assertEquals('Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\ParentClass', $metadatas[1]->getClassName());
                $test->assertEquals('/forum/{category}', $metadatas[1]->getUriSchema());
                $test->assertEquals('stdClass', $metadatas[1]->getExtendedClass());
            }),
            array('valid4.xml', function ($metadatas) use ($test, $serviceConfig) {
                $test->assertCount(1, $metadatas);
                $metadata = $metadatas[0];

                $test->assertEquals('stdClass', $metadata->getClassName());
                $test->assertEquals('/cmf/blog', $metadata->getUriSchema());
                $test->assertEquals($serviceConfig('auto_increment'), $metadata->getConflictResolver());
                $test->assertEquals($serviceConfig('leave_redirect'), $metadata->getDefunctRouteHandler());
            }),
            array('valid5.xml', function ($metadatas) use ($test, $serviceConfig) {
                $test->assertCount(1, $metadatas);
                $metadata = $metadatas[0];

                $test->assertEquals('stdClass', $metadata->getClassName());
                $test->assertEquals('/blog/{category}/{slug}', $metadata->getUriSchema());
                $test->assertEquals($serviceConfig('auto_increment', array('token' => 'category')), $metadata->getConflictResolver());

                $test->assertCount(2, $metadata->getTokenProviders());
                $providers = $metadata->getTokenProviders();
                $test->assertArrayHasKey('category', $providers);
                $test->assertEquals($serviceConfig('method', array('method' => 'getCategoryName')), $providers['category']);
                $test->assertArrayHasKey('slug', $providers);
                $test->assertEquals($serviceConfig('property', array('property' => 'title', 'slugify' => true)), $providers['slug']);
            }),

            // host schema, route options and allowedSchemes
            array('valid7.xml', function ($metadatas) use ($test) {
                $test->assertCount(1, $metadatas);
                $metadata = $metadatas[0];

                $test->assertEquals('stdClass', $metadata->getClassName());
                $test->assertEquals('/blog/{category}/{slug}', $metadata->getUriSchema());
                $test->assertEquals('foobar.dom', $metadata->getHostSchema());
                $test->assertEquals(array('http', 'https'), $metadata->getAllowedSchemes());
                $this->assertEquals(array('bar' => 'foo', 'foo' => 'bar'), $metadata->getRouteOptions());
            }),
        );
    }

    protected function getFixturesPath($fixture)
    {
        return __DIR__.'/../../../Resources/Fixtures/loader_config/'.$fixture;
    }
}
