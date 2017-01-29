<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit;

use Symfony\Cmf\Component\RoutingAuto\UriContext;

class UriContextTest extends \PHPUnit_Framework_TestCase
{
    protected $uriContext;

    public function setUp()
    {
        $this->subject = new \stdClass();
        $this->autoRoute = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
    }

    public function testGetSet()
    {
        $uriContext = new UriContext($this->subject, '/uri/', ['default1' => 'value1'], ['token'], ['conflict'], 'fr');

        // locales
        $this->assertEquals('fr', $uriContext->getLocale());

        /// uri
        $this->assertEquals(null, $uriContext->getUri());
        $uriContext->setUri('/foo/bar');
        $this->assertEquals('/foo/bar', $uriContext->getUri());

        // subject object
        $this->assertEquals($this->subject, $uriContext->getSubject());

        // auto route
        $uriContext->setAutoRoute($this->autoRoute);
        $this->assertEquals($this->autoRoute, $uriContext->getAutoRoute());

        // the translated subject should be initially set as the original subject
        $this->assertSame($this->subject, $uriContext->getTranslatedSubject());
        $transSubject = new \stdClass();
        $uriContext->setTranslatedSubject($transSubject);
        $this->assertSame($transSubject, $uriContext->getTranslatedSubject());

        // uri schema
        $this->assertEquals('/uri/', $uriContext->getUriSchema());

        // token provider configs
        $this->assertEquals(['token'], $uriContext->getTokenProviderConfigs());

        // conflict resolver configs
        $this->assertEquals(['conflict'], $uriContext->getConflictResolverConfig());

        // defaults
        $this->assertEquals(['default1' => 'value1'], $uriContext->getDefaults());
    }
}
