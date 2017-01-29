<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\DefunctRouteHandler;

use Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandler\LeaveRedirectDefunctRouteHandler;

class LeaveRedirectDefunctRouteHandlerTest extends \PHPUnit_Framework_TestCase
{
    protected $adapter;
    protected $uriContextCollection;

    public function setUp()
    {
        $this->adapter = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\AdapterInterface');
        $this->uriContextCollection = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContextCollection');
        $this->route1 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
        $this->route2 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
        $this->route3 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
        $this->route4 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');

        $this->subject = new \stdClass();

        $this->handler = new LeaveRedirectDefunctRouteHandler(
            $this->adapter->reveal()
        );
    }

    public function testLeaveRedirect()
    {
        $this->uriContextCollection->getSubject()->willReturn($this->subject);
        $this->adapter->getReferringAutoRoutes($this->subject)->willReturn([
            $this->route1, $this->route2,
        ]);
        $this->uriContextCollection->containsAutoRoute($this->route1->reveal())->willReturn(true);
        $this->uriContextCollection->containsAutoRoute($this->route2->reveal())->willReturn(false);
        $this->uriContextCollection->containsAutoRoute($this->route3->reveal())->willReturn(true);

        $this->route2->getLocale()->willReturn('fr');
        $this->uriContextCollection->getAutoRouteByLocale('fr')->willReturn($this->route4);

        $this->adapter->createRedirectRoute($this->route2->reveal(), $this->route4->reveal())->shouldBeCalled();

        $this->adapter->migrateAutoRouteChildren($this->route2->reveal(), $this->route4->reveal())->shouldBeCalled();
        $this->handler->handleDefunctRoutes($this->uriContextCollection->reveal());
    }

    public function testLeaveDirectNoTranslation()
    {
        $this->uriContextCollection->getSubject()->willReturn($this->subject);
        $this->adapter->getReferringAutoRoutes($this->subject)->willReturn([
            $this->route1,
        ]);
        $this->uriContextCollection->containsAutoRoute($this->route1->reveal())->willReturn(false);

        $this->route1->getLocale()->willReturn('fr');
        $this->uriContextCollection->getAutoRouteByLocale('fr')->willReturn(null);

        $this->adapter->createRedirectRoute($this->route2->reveal(), $this->route4->reveal())->shouldNotBeCalled();

        $this->adapter->migrateAutoRouteChildren($this->route2->reveal(), $this->route4->reveal())->shouldNotBeCalled();
        $this->handler->handleDefunctRoutes($this->uriContextCollection->reveal());
    }
}
