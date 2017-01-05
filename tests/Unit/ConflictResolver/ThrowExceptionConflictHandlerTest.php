<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\ConflictResolver;

use Symfony\Cmf\Component\RoutingAuto\ConflictResolver\ThrowExceptionConflictResolver;

class ThrowExceptionConflictHandlerTest extends \PHPUnit_Framework_TestCase
{
    protected $adapter;

    public function setUp()
    {
        $this->conflictResolver = new ThrowExceptionConflictResolver();
        $this->uriContext = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContext');
    }

    /**
     * @expectedException \Symfony\Cmf\Component\RoutingAuto\ConflictResolver\Exception\ExistingUriException
     * @expectedExceptionMessage There already exists an auto route for URL "/foobar"
     */
    public function testResolveConflict()
    {
        $this->uriContext->getUri()->willReturn('/foobar');
        $this->conflictResolver->resolveConflict($this->uriContext->reveal());
    }
}
