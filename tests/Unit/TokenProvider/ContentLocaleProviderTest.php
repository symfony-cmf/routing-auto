<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\TokenProvider;

use Symfony\Cmf\Component\RoutingAuto\TokenProvider\ContentLocaleProvider;

class ContentLocaleProviderTest extends \PHPUnit_Framework_TestCase
{
    protected $uriContext;

    public function setUp()
    {
        $this->uriContext = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContext');
        $this->provider = new ContentLocaleProvider();
    }

    public function testGetValue()
    {
        $this->uriContext->getLocale()->willReturn('de');
        $res = $this->provider->provideValue($this->uriContext->reveal(), []);
        $this->assertEquals('de', $res);
    }
}
