<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\ConflictResolver;

use Symfony\Cmf\Component\RoutingAuto\ConflictResolver\AutoIncrementConflictResolver;

class AutoIncrementConflictResolverTest extends \PHPUnit_Framework_TestCase
{
    protected $adapter;

    public function setUp()
    {
        $this->adapter = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\AdapterInterface');

        $this->conflictResolver = new AutoIncrementConflictResolver($this->adapter->reveal());
        $this->uriContext = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContext');
    }

    public function provideResolveConflict()
    {
        return array(
            array(
                '/foobar/bar',
                array(
                    '/foobar/bar-1',
                ),
                '/foobar/bar-2',
            ),
            array(
                '/foobar/bar',
                array(
                    '/foobar/bar-1',
                    '/foobar/bar-2',
                    '/foobar/bar-4',
                ),
                '/foobar/bar-3',
            ),
        );
    }

    /**
     * @dataProvider provideResolveConflict
     */
    public function testResolveConflict($uri, $existingRoutes, $expectedResult)
    {
        $this->uriContext->getUri()->willReturn($uri);

        foreach ($existingRoutes as $existingRoute) {
            $this->adapter->findRouteForUri($existingRoute, $this->uriContext->reveal())->willReturn(new \stdClass());
        }
        $this->adapter->findRouteForUri($expectedResult, $this->uriContext->reveal())->willReturn(null);

        $uri = $this->conflictResolver->resolveConflict($this->uriContext->reveal());
        $this->assertEquals($expectedResult, $uri);
    }
}
