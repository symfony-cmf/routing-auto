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

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\TokenProvider;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Cmf\Component\RoutingAuto\TokenProvider\ContentLocaleProvider;
use Symfony\Cmf\Component\RoutingAuto\UriContext;

class ContentLocaleProviderTest extends TestCase
{
    /**
     * @var UriContext|ObjectProphecy
     */
    private $uriContext;

    /**
     * @var ContentLocaleProvider
     */
    private $provider;

    public function setUp()
    {
        $this->uriContext = $this->prophesize(UriContext::class);
        $this->provider = new ContentLocaleProvider();
    }

    public function testGetValue()
    {
        $this->uriContext->getLocale()->willReturn('de');
        $res = $this->provider->provideValue($this->uriContext->reveal(), []);
        $this->assertEquals('de', $res);
    }
}
