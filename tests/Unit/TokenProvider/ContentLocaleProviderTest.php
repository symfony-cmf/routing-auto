<?php

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\TokenProvider;

use Symfony\Cmf\Component\RoutingAuto\Tests\Unit\BaseTestCase;
use Symfony\Cmf\Component\RoutingAuto\TokenProvider\ContentLocaleProvider;

class ContentLocaleProviderTest extends BaseTestCase
{
    protected $slugifier;
    protected $article;
    protected $urlContext;

    public function setUp()
    {
        parent::setUp();

        $this->urlContext = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UrlContext');
        $this->provider = new ContentLocaleProvider($this->slugifier->reveal());
    }

    public function testGetValue()
    {
        $this->urlContext->getLocale()->willReturn('de');
        $res = $this->provider->provideValue($this->urlContext->reveal(), array());
        $this->assertEquals('de', $res);
    }
}

