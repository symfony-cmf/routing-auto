<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\ConflictResolver;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Cmf\Component\RoutingAuto\ConflictResolver\ThrowExceptionConflictResolver;
use Symfony\Cmf\Component\RoutingAuto\UriContext;

class ThrowExceptionConflictHandlerTest extends TestCase
{
    /**
     * @var ThrowExceptionConflictResolver
     */
    private $conflictResolver;

    /**
     * @var UriContext|ObjectProphecy
     */
    private $uriContext;

    public function setUp()
    {
        $this->conflictResolver = new ThrowExceptionConflictResolver();
        $this->uriContext = $this->prophesize(UriContext::class);
    }

    public function testResolveConflict()
    {
        $this->expectException(\Symfony\Cmf\Component\RoutingAuto\ConflictResolver\Exception\ExistingUriException::class);
        $this->expectExceptionMessage('There already exists an auto route for URL "/foobar"');

        $this->uriContext->getUri()->willReturn('/foobar');
        $this->conflictResolver->resolveConflict($this->uriContext->reveal());
    }
}
