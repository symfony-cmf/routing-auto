<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit;

use Symfony\Cmf\Component\RoutingAuto\UriContext;
use Symfony\Cmf\Component\RoutingAuto\Tests\Unit\BaseTestCase;

class UriContextTest extends BaseTestCase
{
    protected $uriContext;

    public function setUp()
    {
        parent::setUp();
        $this->subjectObject = new \stdClass;
        $this->autoRoute = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
    }

    public function testGetSet()
    {
        $uriContext = new UriContext($this->subjectObject, 'fr');

        // locales
        $this->assertEquals('fr', $uriContext->getLocale());

        /// uri
        $this->assertEquals(null, $uriContext->getUri());
        $uriContext->setUri('/foo/bar');
        $this->assertEquals('/foo/bar', $uriContext->getUri());

        // subject object
        $this->assertEquals($this->subjectObject, $uriContext->getSubjectObject());

        // auto route
        $uriContext->setAutoRoute($this->autoRoute);
        $this->assertEquals($this->autoRoute, $uriContext->getAutoRoute());
    }
}

