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
use Symfony\Cmf\Component\RoutingAuto\Tests\Unit\BaseTestCase;

class ClassMetadataTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->classMetadata = new ClassMetadata('\stdClass');
    }

    public function provideGetSet()
    {
        return array(
            array(
                'uriSchema', '/path/to',
            ),
            array(
                'hostSchema', 'foo.bar',
            ),
            array(
                'routeOptions', array('foo' => 'bar'),
            ),
            array(
                'allowedSchemes', array('http', 'https'),
            ),
        );
    }

    /**
     * @dataProvider provideGetSet
     */
    public function testGetSet($field, $value)
    {
        $getter = 'get' . ucfirst($field);
        $setter = 'set' . ucfirst($field);

        $this->classMetadata->$setter($value);
        $res = $this->classMetadata->$getter();

        $this->assertEquals($value, $res);
    }
}

