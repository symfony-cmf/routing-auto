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

use Symfony\Cmf\Component\RoutingAuto\AdapterInterface;
use Symfony\Cmf\Component\RoutingAuto\AutoRouteManager;
use Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandlerInterface;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContext;
use Symfony\Cmf\Component\RoutingAuto\UriContextCollection;
use Symfony\Cmf\Component\RoutingAuto\UriContextCollectionBuilder;
use Symfony\Cmf\Component\RoutingAuto\UriGeneratorInterface;

/**
 * @testdox The manager
 */
class AutoRouteManagerTest extends \PHPUnit_Framework_TestCase
{
    private $adapter;
    private $uriGenerator;
    private $defunctRouteHandler;
    private $collectionBuilder;
    private $manager;

    public function setUp()
    {
        $this->adapter = $this->prophesize(AdapterInterface::class);
        $this->uriGenerator = $this->prophesize(UriGeneratorInterface::class);
        $this->defunctRouteHandler = $this->prophesize(DefunctRouteHandlerInterface::class);
        $this->collectionBuilder = $this->prophesize(UriContextCollectionBuilder::class);

        $this->manager = new AutoRouteManager(
            $this->adapter->reveal(),
            $this->uriGenerator->reveal(),
            $this->defunctRouteHandler->reveal(),
            $this->collectionBuilder->reveal()
        );
    }

    /**
     * Provides the routes configuration for each tested use case.
     *
     * Each dataset is an array of routes configuration. Each configuration
     * is an associative array containing:
     *  - generatedUri (string): the URI made by the URI generator for this route,
     *  - locale (string): the locale
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
                        'locale' => null,
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'expectedUri' => '/foo/bar',
                    ],
                ],
            ],
            'a single localized route' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => 'fr',
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'expectedUri' => '/foo/bar',
                    ],
                ],
            ],
            'two routes' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => null,
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'expectedUri' => '/foo/bar',
                    ],
                    [
                        'generatedUri' => '/bar/baz',
                        'locale' => null,
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'expectedUri' => '/bar/baz',
                    ],
                ],
            ],
            'two localized routes' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => 'en',
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'expectedUri' => '/foo/bar',
                    ],
                    [
                        'generatedUri' => '/bar/baz',
                        'locale' => 'fr',
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'expectedUri' => '/bar/baz',
                    ],
                ],
            ],
            'a single route conflicting with a persisted one' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => null,
                        'existsInDatabase' => true,
                        'withSameContent' => false,
                        'expectedUri' => '/foo/bar-resolved',
                    ],
                ],
            ],
            'a single localized route conflicting with a persisted one' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => 'fr',
                        'existsInDatabase' => true,
                        'withSameContent' => false,
                        'expectedUri' => '/foo/bar-resolved',
                    ],
                ],
            ],
            'two conflicting routes' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => null,
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'expectedUri' => '/foo/bar',
                    ],
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => null,
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'expectedUri' => '/foo/bar-resolved',
                    ],
                ],
            ],
            'two localized conflicting routes' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => 'en',
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'expectedUri' => '/foo/bar',
                    ],
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => 'fr',
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'expectedUri' => '/foo/bar-resolved',
                    ],
                ],
            ],
        ];
    }

    /**
     * Configure the URI generator.
     *
     * The URI generator stub:
     *  - generates the provided URI,
     *  - if the expected URI is different from the generated one, resolves the conflict.
     */
    private function configureUriGenerator($context, $route)
    {
        $this->uriGenerator->generateUri($context)->willReturn($route['generatedUri']);

        if ($route['expectedUri'] !== $route['generatedUri']) {
            $this->uriGenerator->resolveConflict($context)->willReturn($route['expectedUri']);
        }
    }

    /**
     * Configure the adapter.
     *
     * The adapter stub:
     *  - generates the tag,
     *  - creates a new autoroute,
     *  - if the route exists in the database, finds it,
     *  - tells if the existing route matches the same content,
     *  - if the route specify a locale, translates the content.
     */
    private function configureAdapter($context, $route)
    {
        $tag = 'tag';
        $subject = $context->reveal()->getSubject();

        $this->adapter->generateAutoRouteTag($context)->willReturn($tag);

        $this->adapter->createAutoRoute($context, $subject, $tag)
            ->willReturn($this->prophesize(AutoRouteInterface::class));

        if ($route['existsInDatabase']) {
            $existingAutoRoute = $this->prophesize(AutoRouteInterface::class);

            $this->adapter->findRouteForUri(
                $route['generatedUri'],
                $context
            )->willReturn($existingAutoRoute);

            $this->adapter->compareAutoRouteContent(
                $existingAutoRoute->reveal(),
                $subject
            )->willReturn($route['withSameContent'] === true);
        } else {
            $this->adapter->findRouteForUri($route['generatedUri'], $context)->willReturn(null);
        }

        if (!is_null($route['locale'])) {
            $translatedSubject = new \stdClass();

            $this->adapter->translateObject($subject, $route['locale'])->willReturn($translatedSubject);
        } else {
            $this->adapter->translateObject($subject, $route['locale'])->willReturn($subject);
        }
    }

    /**
     * @testdox builds the collection with
     * @dataProvider routesConfigurations
     */
    public function buildUriContextCollection($routes)
    {
        $collection = $this->prophesize(UriContextCollection::class);
        $subject = new \stdClass();

        // Build the context mocks and configure the stubs behavior
        // regarding each route
        $contexts = [];

        foreach ($routes as $route) {
            $context = $this->prophesize(UriContext::class);

            // Configure the context mock
            $context->getLocale()->willReturn($route['locale']);
            $context->getSubject()->willReturn($subject);

            // Configure the stubs
            $this->configureUriGenerator($context, $route);
            $this->configureAdapter($context, $route);

            // If the route exists within the database and matches the same
            // content, it is reused. Otherwize, a new one is expected.
            if ($route['existsInDatabase'] and $route['withSameContent']) {
                $existingAutoRoute = $this->adapter->reveal()->findRouteForUri(
                    $route['generatedUri'],
                    $context->reveal()
                );

                $context->setAutoRoute($existingAutoRoute)->shouldBeCalled();
            } else {
                $tag = $this->adapter->reveal()->generateAutoRouteTag($context->reveal());
                $newAutoRoute = $this->adapter->reveal()->createAutoRoute(
                    $context->reveal(),
                    $subject,
                    $tag
                );

                $context->setAutoRoute($newAutoRoute)->shouldBeCalled();
            }

            // If the route specify a locale, the translated subject is put in
            // the context
            if (!is_null($route['locale'])) {
                $translatedSubject = $this->adapter->reveal()->translateObject(
                    $subject,
                    $route['locale']
                );

                $context->setTranslatedSubject($translatedSubject)->shouldBeCalled();
            }

            // Expect the generated URI
            $context->setUri($route['generatedUri'])->shouldBeCalled();

            // Expect the URI
            $context->setUri($route['expectedUri'])->shouldBeCalled();

            $contexts[] = $context;
        }

        // Configure the collection stub
        $collection->getUriContexts()->willReturn($contexts);
        $collection->getSubject()->willReturn($subject);

        // Run the tested method
        $this->manager->buildUriContextCollection($collection->reveal());

        // The defunct routes handler handles the defunct routes after the
        // processing of the contexts collection.
        // This should be done in a depending test. But PHPUnit does not
        // allow a depending test to receive the result of a test which use
        // a data provider.
        $this->defunctRouteHandler->handleDefunctRoutes($collection->reveal())->shouldBeCalled();
        $this->manager->handleDefunctRoutes();
    }
}
