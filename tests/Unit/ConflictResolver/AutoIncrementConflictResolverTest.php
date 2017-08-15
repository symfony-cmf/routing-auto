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

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Cmf\Component\RoutingAuto\AdapterInterface;
use Symfony\Cmf\Component\RoutingAuto\ConflictResolver\AutoIncrementConflictResolver;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContext;
use Symfony\Cmf\Component\RoutingAuto\UriContextCollection;

class AutoIncrementConflictResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AdapterInterface|ObjectProphecy
     */
    private $adapter;

    /**
     * @var UriContext|ObjectProphecy
     */
    private $uriContext;

    /**
     * @var UriContextCollection|ObjectProphecy
     */
    private $contextCollection;

    /**
     * @var AutoIncrementConflictResolver
     */
    private $conflictResolver;

    public function setUp()
    {
        $this->adapter = $this->prophesize(AdapterInterface::class);
        $this->uriContext = $this->prophesize(UriContext::class);
        $this->contextCollection = $this->prophesize(UriContextCollection::class);

        $this->uriContext->getCollection()->willReturn($this->contextCollection);

        $this->conflictResolver = new AutoIncrementConflictResolver($this->adapter->reveal());
    }

    /**
     * Provide the URIs present in the collection and the database.
     *
     * - uri (string): the conflicting URI
     * - collection (array): the URIs of the routes contained in the collection
     * - datadase (array): the URIs of the routes contained in the database
     * - expectedUri (string): the expected URI after the conflict has been resolved
     */
    public function provideResolveConflict()
    {
        return [
            'a not conflicting URI' => [
                'uri' => '/foobar/bar',
                'collection' => [],
                'database' => [],
                'expectedUri' => '/foobar/bar',
            ],
            'a conflict with one URI in the collection' => [
                'uri' => '/foobar/bar',
                'collection' => [
                    '/foobar/bar',
                ],
                'database' => [],
                'expectedUri' => '/foobar/bar-1',
            ],
            'a conflict with one URI in the database' => [
                'uri' => '/foobar/bar',
                'collection' => [],
                'database' => [
                    '/foobar/bar',
                ],
                'expectedUri' => '/foobar/bar-1',
            ],
            'a conflict with three consecutive URIs in the collection' => [
                'uri' => '/foobar/bar',
                'collection' => [
                    '/foobar/bar',
                    '/foobar/bar-1',
                    '/foobar/bar-2',
                ],
                'database' => [],
                'expectedUri' => '/foobar/bar-3',
            ],
            'a conflict with three consecutive URIs in the database' => [
                'uri' => '/foobar/bar',
                'collection' => [],
                'database' => [
                    '/foobar/bar',
                    '/foobar/bar-1',
                    '/foobar/bar-2',
                ],
                'expectedUri' => '/foobar/bar-3',
            ],
            'a conflict with four consecutive URIs in both the collection and the database' => [
                'uri' => '/foobar/bar',
                'collection' => [
                    '/foobar/bar',
                    '/foobar/bar-2',
                ],
                'database' => [
                    '/foobar/bar-1',
                    '/foobar/bar-3',
                ],
                'expectedUri' => '/foobar/bar-4',
            ],
            'a conflict with three not consecutive URIs in the collection' => [
                'uri' => '/foobar/bar',
                'collection' => [
                    '/foobar/bar',
                    '/foobar/bar-1',
                    '/foobar/bar-2',
                    '/foobar/bar-4',
                ],
                'database' => [],
                'expectedUri' => '/foobar/bar-3',
            ],
            'a conflict with three not consecutive URIs in the database' => [
                'uri' => '/foobar/bar',
                'collection' => [],
                'database' => [
                    '/foobar/bar',
                    '/foobar/bar-1',
                    '/foobar/bar-2',
                    '/foobar/bar-4',
                ],
                'expectedUri' => '/foobar/bar-3',
            ],
            'a conflict with four not consecutive URIs in both the collection and the database' => [
                'uri' => '/foobar/bar',
                'collection' => [
                    '/foobar/bar',
                    '/foobar/bar-2',
                ],
                'database' => [
                    '/foobar/bar-1',
                    '/foobar/bar-4',
                ],
                'expectedUri' => '/foobar/bar-3',
            ],
        ];
    }

    /**
     * @dataProvider provideResolveConflict
     */
    public function testResolveConflict(
        $uri,
        array $collectionUris,
        array $databaseUris,
        $expectedUri
    ) {
        $this->uriContext->getUri()->willReturn($uri);

        $this->configureCollection($collectionUris);
        $this->configureAdapter($databaseUris);

        $uri = $this->conflictResolver->resolveConflict($this->uriContext->reveal());

        $this->assertEquals($expectedUri, $uri);
    }

    /**
     * Configure the context collection.
     *
     * The context collection stub finds an auto route using the URI generated
     * for a context it contains.
     */
    private function configureCollection(array $uris)
    {
        $this->contextCollection->getAutoRouteByUri(Argument::type('string'))
            ->willReturn(null);

        foreach ($uris as $uri) {
            $this->contextCollection->getAutoRouteByUri($uri)
                ->willReturn($this->prophesize(AutoRouteInterface::class)->reveal());
        }
    }

    /**
     * Configure the adapter.
     *
     * The adapter stub finds an auto route which matches for a given URI.
     */
    private function configureAdapter(array $uris)
    {
        $this->adapter->findRouteForUri(Argument::type('string'), $this->uriContext->reveal())
            ->willReturn(null);

        foreach ($uris as $uri) {
            $this->adapter->findRouteForUri($uri, $this->uriContext->reveal())
                ->willReturn($this->prophesize(AutoRouteInterface::class)->reveal());
        }
    }
}
