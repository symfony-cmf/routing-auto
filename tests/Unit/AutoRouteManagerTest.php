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

use Symfony\Cmf\Component\RoutingAuto\AutoRouteManager;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContext;

/**
 * @testdox The manager
 */
class AutoRouteManagerTest extends \PHPUnit_Framework_TestCase
{
    private $adapter;
    private $uriGenerator;
    private $defunctRouteHandler;
    private $collectionBuilder;

    public function setUp()
    {
        $this->adapter = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\AdapterInterface');
        $this->uriGenerator = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriGeneratorInterface');
        $this->defunctRouteHandler = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandlerInterface');
        $this->collectionBuilder = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContextCollectionBuilder');

        $this->manager = new AutoRouteManager(
            $this->adapter->reveal(),
            $this->uriGenerator->reveal(),
            $this->defunctRouteHandler->reveal(),
            $this->collectionBuilder->reveal()
        );

        $this->collection = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContextCollection');
        $this->context1 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContext');
        $this->context2 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContext');
        $this->autoRoute1 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
        $this->autoRoute2 = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');

        $this->subject = new \stdClass();
    }

    /**
     * It should delegate the generation of the collection and resolve the URIs
     * in it (for non-existing URIs).
     * It should handle defunct routes.
     */
    public function testBuildCollection()
    {
        $this->prepareBuildCollection();

        $this->context1->getLocale()->willReturn(null);
        $this->context2->getLocale()->willReturn(null);

        $this->manager->buildUriContextCollection($this->collection->reveal());

        // test handle defunt routes:
        // would like to make this a separate test with @depends
        // but PHPUnit does not allow it.
        $this->defunctRouteHandler->handleDefunctRoutes($this->collection->reveal())->shouldBeCalled();
        $this->manager->handleDefunctRoutes();
    }

    /**
     * It should translate subject objects.
     */
    public function testBuildCollectionTranslate()
    {
        $this->prepareBuildCollection();
        $translatedSubject = new \stdClass();

        $this->context1->getLocale()->willReturn('fr');
        $this->context2->getLocale()->willReturn('de');
        $this->adapter->translateObject($this->subject, 'fr')->willReturn($translatedSubject)->shouldBeCalled();
        $this->adapter->translateObject($this->subject, 'de')->willReturn($this->subject)->shouldBeCalled();
        $this->context1->setTranslatedSubject($this->subject)->shouldBeCalled();

        $this->manager->buildUriContextCollection($this->collection->reveal());
    }

    private function prepareBuildCollection()
    {
        $this->collectionBuilder->build($this->collection->reveal());
        $this->collection->getUriContexts()->willReturn([
            $this->context1->reveal(),
            $this->context2->reveal(),
        ]);
        $this->collection->getSubject()->willReturn($this->subject);

        for ($index = 1; $index <= 2; ++$index) {
            $contextVar = 'context'.$index;
            $uri = '/uri'.$index;
            $autoRouteVar = 'autoRoute'.$index;

            $this->uriGenerator->generateUri($this->{$contextVar}->reveal())->willReturn($uri);
            $this->{$contextVar}->getSubject()->willReturn($this->subject);
            $this->{$contextVar}->setUri($uri)->shouldBeCalled();

            $this->adapter->findRouteForUri($uri, $this->{$contextVar})->willReturn(null);
            $this->adapter->generateAutoRouteTag($this->{$contextVar}->reveal())->willReturn('fr');
            $this->adapter->createAutoRoute($this->{$contextVar}, $this->subject, 'fr')->willReturn($this->{$autoRouteVar}->reveal());
            $this->{$contextVar}->setAutoRoute($this->{$autoRouteVar}->reveal())->shouldBeCalled();
        }
    }

    /**
     * It should handle existing URIs.
     *
     * @dataProvider provideBuildCollectionExisting
     */
    public function testBuildCollectionExisting($sameContent)
    {
        $uri = '/uri/to';
        $resolvedUri = '/resolved/uri';

        $this->collectionBuilder->build($this->collection->reveal());
        $this->collection->getUriContexts()->willReturn([
            $this->context1->reveal(),
        ]);
        $this->collection->getSubject()->willReturn($this->subject);
        $this->uriGenerator->generateUri($this->context1->reveal())->willReturn($uri);
        $this->context1->setUri($uri)->shouldBeCalled();
        $this->context1->getLocale()->willReturn(null);
        $this->context1->getSubject()->willReturn($this->subject);
        $this->adapter->findRouteForUri($uri, $this->context1)->willReturn(
            $this->autoRoute1->reveal()
        );

        // handle existing route
        $this->adapter->compareAutoRouteContent(
            $this->autoRoute1->reveal(),
            $this->subject
        )->willReturn($sameContent);

        $this->context1->getSubject()->willReturn($this->subject);

        if ($sameContent) {
            $this->autoRoute1->setType(AutoRouteInterface::TYPE_PRIMARY)
                ->shouldBeCalled();
        } else {
            $this->uriGenerator->resolveConflict($this->context1->reveal())
                ->willReturn($resolvedUri);
            $this->context1->setUri($resolvedUri)->shouldBeCalled();
            $this->adapter->generateAutoRouteTag($this->context1->reveal())->willReturn('fr');
            $this->adapter->createAutoRoute($this->context1, $this->subject, 'fr')->willReturn($this->autoRoute1->reveal());
        }

        $this->context1->setAutoRoute($this->autoRoute1->reveal())->shouldBeCalled();

        $this->manager->buildUriContextCollection($this->collection->reveal());
    }

    public function provideBuildCollectionExisting()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * Provides the routes configuration for each tested use case.
     *
     * Each dataset is an array of routes configuration. Each configuration
     * is an associative array containing:
     *  - generatedUri (string): the URI made by the URI generator for this route,
     *  - existsInDatabase (boolean): is there an existing persisted route matching the same URI,
     *  - expectedUri (string): the URI expected to be set on the current route.
     *
     * If the generated URI and the expected one are different, it means that
     * the conflict resolver should be called by the manager.
     */
    public function routesConfigurations()
    {
        return [
            'a single route' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'expectedUri' => '/foo/bar'
                    ]
                ]
            ],
            'two routes' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'expectedUri' => '/foo/bar'
                    ],
                    [
                        'generatedUri' => '/bar/baz',
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'expectedUri' => '/bar/baz'
                    ]
                ]
            ],
            'a single route conflicting with a persisted one' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'existsInDatabase' => true,
                        'withSameContent' => false,
                        'expectedUri' => '/foo/bar-resolved'
                    ]
                ]
            ],
            'two conflicting routes' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'expectedUri' => '/foo/bar'
                    ],
                    [
                        'generatedUri' => '/foo/bar',
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'expectedUri' => '/foo/bar-resolved'
                    ]
                ]
            ]
        ];
    }

    /**
     * @testdox builds the collection with
     * @dataProvider routesConfigurations
     */
    public function buildUriContextCollection($routes)
    {
        $subject = new \stdClass();

        // Build the context mocks and configure the stubs behavior
        // regarding each route
        $contexts = [];

        foreach ($routes as $route) {
            $context = $this->prophesize(UriContext::class);
            $tag = 'tag';
            $newAutoRoute = $this->prophesize(AutoRouteInterface::class);
            $existingAutoRoute = $this->prophesize(AutoRouteInterface::class);

            $context->getLocale()->willReturn(null);
            $context->getSubject()->willReturn($subject);
            
            // If the route exists within the database and matches the same
            // content, it is reused. Otherwize, a new one is expected.
            if ($route['existsInDatabase'] and $route['withSameContent']) {
                $context->setAutoRoute($existingAutoRoute)->shouldBeCalled();
            } else {
                $context->setAutoRoute($newAutoRoute)->shouldBeCalled();
            }

            // Expect generated URI
            $context->setUri($route['generatedUri'])->shouldBeCalled();

            // Expect the URI
            $context->setUri($route['expectedUri'])->shouldBeCalled();

            // The URI generator stub:
            //  - generates the provided URI,
            $this->uriGenerator->generateUri($context->reveal())->willReturn($route['generatedUri']);
            //  - if the expected URI is different from the generated one,
            //    resolves the conflict.
            if ($route['expectedUri'] !== $route['generatedUri']) {
                $this->uriGenerator->resolveConflict($context->reveal())->willReturn($route['expectedUri']);
            }

            // The adapter:
            //  - generates the tag,
            $this->adapter->generateAutoRouteTag($context)->willReturn($tag);
            //  - creates a new autoroute,
            $this->adapter->createAutoRoute($context, $subject, $tag)
                ->willReturn($newAutoRoute);
            //  - if the route exists in database, finds it,
            if ($route['existsInDatabase']) {
                $this->adapter->findRouteForUri($route['generatedUri'], $context)->willReturn($existingAutoRoute);

                //  - tells if the existing route matches the same content
                $this->adapter->compareAutoRouteContent(
                    $existingAutoRoute->reveal(),
                    $subject
                )->willReturn($route['withSameContent'] === true);
            } else {
                $expectedAutoRoute =  $newAutoRoute;
                $this->adapter->findRouteForUri($route['generatedUri'], $context)->willReturn(null);
            }

            $contexts[] = $context;
        }

        // Configure the collection stub
        $this->collection->getUriContexts()->willReturn($contexts);
        $this->collection->getSubject()->willReturn($subject);

        // Run the tested method
        $this->manager->buildUriContextCollection($this->collection->reveal());
    }
}
