<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit;

use Symfony\Cmf\Component\RoutingAuto\AutoRouteManager;
use Symfony\Cmf\Component\RoutingAuto\UriContextCollection;

class AutoRouteManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->driver = $this->getMock('Symfony\Cmf\Component\RoutingAuto\AdapterInterface');
        $this->uriGenerator = $this->getMock('Symfony\Cmf\Component\RoutingAuto\UriGeneratorInterface');
        $this->defunctRouteHandler = $this->getMock('Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandlerInterface');
        $this->autoRouteManager = new AutoRouteManager(
            $this->driver,
            $this->uriGenerator,
            $this->defunctRouteHandler
        );
    }

    public function provideBuildUriContextCollection()
    {
        return array(
            array(
                array(
                    'locales' => array('en', 'fr', 'de', 'be'),
                    'uris' => array(
                        '/en/this-is-an-route' => array('conflict' => false),
                        '/fr/this-is-an-route' => array('conflict' => false),
                        '/de/this-is-an-route' => array('conflict' => false),
                        '/be/this-is-an-route' => array('conflict' => false),
                    ),
                    'existingRoute' => false,
                ),
            ),
        );
    }

    /**
     * @dataProvider provideBuildUriContextCollection
     */
    public function testBuildUriContextCollection($params)
    {
        $params = array_merge(array(
            'locales' => array(),
            'uris' => array(),
        ), $params);

        $this->driver->expects($this->once())
            ->method('getLocales')
            ->will($this->returnValue($params['locales']));

        $localesCount = count($params['locales']);
        $uris = $params['uris'];
        $indexedUris = array_keys($uris);
        $expectedRoutes = array();
        $document = new \stdClass();

        for ($i = 0; $i < $localesCount; ++$i) {
            $expectedRoutes[] = $this->getMock('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');

            $this->uriGenerator->expects($this->exactly($localesCount))
                ->method('generateUri')
                ->will($this->returnCallback(function () use ($i, $indexedUris) {
                    return $indexedUris[$i];
                }));
        }

        $this->driver->expects($this->exactly($localesCount))
            ->method('createAutoRoute')
            ->will($this->returnCallback(function ($uri, $document) use ($expectedRoutes) {
                static $i = 0;

                return $expectedRoutes[$i++];
            }));

        $uriContextCollection = new UriContextCollection($document);
        $this->autoRouteManager->buildUriContextCollection($uriContextCollection);

        foreach ($expectedRoutes as $expectedRoute) {
            $this->assertTrue($uriContextCollection->containsAutoRoute($expectedRoute), 'URL context collection contains route: '.spl_object_hash($expectedRoute));
        }
    }
}
