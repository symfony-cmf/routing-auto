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

use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContext;
use Symfony\Cmf\Component\RoutingAuto\UriContextCollection;

class UriContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var object
     */
    private $subject;

    /**
     * @var UriContextCollection|ObjectProphecy
     */
    private $contextCollection;

    /**
     * @var AutoRouteInterface|ObjectProphecy
     */
    private $autoRoute;

    public function setUp()
    {
        $this->subject = new \stdClass();

        $this->contextCollection = $this->prophesize(UriContextCollection::class);
        $this->contextCollection->getSubject()->willReturn($this->subject);

        $this->autoRoute = $this->prophesize(AutoRouteInterface::class)->reveal();
    }

    public function testGetSet()
    {
        $uri = '/foo/bar';
        $uriSchema = '/uri';
        $defaults = ['default1' => 'value1'];
        $tokenProvidersConfiguration = ['token'];
        $conflictResolverConfiguration = ['conflict'];
        $locale = 'fr';
        $translatedSubject = new \stdClass();

        $uriContext = new UriContext(
            $this->contextCollection->reveal(),
            $uriSchema,
            $defaults,
            $tokenProvidersConfiguration,
            $conflictResolverConfiguration,
            $locale
        );

        // collection
        $this->assertSame($this->contextCollection->reveal(), $uriContext->getCollection());

        // locale
        $this->assertEquals($locale, $uriContext->getLocale());

        /// URI
        $this->assertEquals(null, $uriContext->getUri());
        $uriContext->setUri($uri);
        $this->assertEquals($uri, $uriContext->getUri());

        // subject object
        $this->assertEquals($this->subject, $uriContext->getSubject());

        // auto route
        $uriContext->setAutoRoute($this->autoRoute);
        $this->assertEquals($this->autoRoute, $uriContext->getAutoRoute());

        // the translated subject should be initially set as the original subject
        $this->assertSame($this->subject, $uriContext->getTranslatedSubject());
        $uriContext->setTranslatedSubject($translatedSubject);
        $this->assertSame($translatedSubject, $uriContext->getTranslatedSubject());

        // URI schema
        $this->assertEquals($uriSchema, $uriContext->getUriSchema());

        // token providers configuration
        $this->assertEquals($tokenProvidersConfiguration, $uriContext->getTokenProviderConfigs());

        // conflict resolver configuration
        $this->assertEquals($conflictResolverConfiguration, $uriContext->getConflictResolverConfig());

        // defaults
        $this->assertEquals($defaults, $uriContext->getDefaults());
    }
}
