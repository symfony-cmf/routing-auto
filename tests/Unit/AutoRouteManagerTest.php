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

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Cmf\Component\RoutingAuto\AdapterInterface;
use Symfony\Cmf\Component\RoutingAuto\AutoRouteManager;
use Symfony\Cmf\Component\RoutingAuto\DefunctRouteHandlerInterface;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContext;
use Symfony\Cmf\Component\RoutingAuto\UriContextCollection;
use Symfony\Cmf\Component\RoutingAuto\UriContextCollectionBuilder;
use Symfony\Cmf\Component\RoutingAuto\UriGeneratorInterface;

class AutoRouteManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AdapterInterface|ObjectProphecy
     */
    private $adapter;

    /**
     * @var UriGeneratorInterface|ObjectProphecy
     */
    private $uriGenerator;

    /**
     * @var DefunctRouteHandlerInterface|ObjectProphecy
     */
    private $defunctRouteHandler;

    /**
     * @var UriContextCollectionBuilder|ObjectProphecy
     */
    private $collectionBuilder;

    /**
     * @var AutoRouteManager|ObjectProphecy
     */
    private $manager;
    private $subject;
    private $collection;
    private $translatedSubjects;
    private $databaseAutoRoutes;

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

        $this->subject = new \stdClass();
        $this->collection = new UriContextCollection($this->subject);
        $this->translatedSubjects = [];
        $this->databaseAutoRoutes = [];
    }

    /**
     * Provides the routes configuration for each tested use case.
     *
     * Each dataset is an array of routes configuration. Each configuration
     * is an associative array containing:
     *  - generatedUri (string): the URI made by the URI generator for this route,
     *  - locale (string): the locale
     *  - existsInDatabase (boolean): is there an existing persisted route matching the same URI,
     *  - withSameContent (boolean): is the existing route referring to the same content,
     *  - forSameLocale (boolean): is the existing route referring to the same content for the same locale,
     *  - expectedUri (string): the URI expected to be set on the current route.
     *
     * If the generated URI and the expected one are different, it means that
     * the conflict resolver should be called by the manager.
     */
    public function provideBuildUriContextCollection()
    {
        return [
            'one route' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => null,
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'forSameLocale' => false,
                        'expectedUri' => '/foo/bar',
                    ],
                ],
            ],
            'one localized route' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => 'fr',
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'forSameLocale' => false,
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
                        'forSameLocale' => false,
                        'expectedUri' => '/foo/bar',
                    ],
                    [
                        'generatedUri' => '/bar/baz',
                        'locale' => null,
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'forSameLocale' => false,
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
                        'forSameLocale' => false,
                        'expectedUri' => '/foo/bar',
                    ],
                    [
                        'generatedUri' => '/bar/baz',
                        'locale' => 'fr',
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'forSameLocale' => false,
                        'expectedUri' => '/bar/baz',
                    ],
                ],
            ],
            'one route conflicting with a persisted one' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => null,
                        'existsInDatabase' => true,
                        'withSameContent' => false,
                        'forSameLocale' => false,
                        'expectedUri' => '/foo/bar-resolved',
                    ],
                ],
            ],
            'one route conflicting with a persisted one referencing the same content' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => null,
                        'existsInDatabase' => true,
                        'withSameContent' => true,
                        'forSameLocale' => false,
                        'expectedUri' => '/foo/bar',
                    ],
                ],
            ],
            'one localized route conflicting with a persisted one' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => 'fr',
                        'existsInDatabase' => true,
                        'withSameContent' => false,
                        'forSameLocale' => false,
                        'expectedUri' => '/foo/bar-resolved',
                    ],
                ],
            ],
            'one localized route conflicting with a persisted one referencing the same content' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => 'fr',
                        'existsInDatabase' => true,
                        'withSameContent' => true,
                        'forSameLocale' => false,
                        'expectedUri' => '/foo/bar-resolved',
                    ],
                ],
            ],
            'one localized route conflicting with a persisted one referencing the same content for the same locale' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => 'fr',
                        'existsInDatabase' => true,
                        'withSameContent' => true,
                        'forSameLocale' => true,
                        'expectedUri' => '/foo/bar',
                    ],
                ],
            ],
            'two mutually conflicting routes' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => null,
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'forSameLocale' => false,
                        'expectedUri' => '/foo/bar',
                    ],
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => null,
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'forSameLocale' => false,
                        'expectedUri' => '/foo/bar',
                    ],
                ],
            ],
            'two mutually conflicting localized routes' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => 'en',
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'forSameLocale' => false,
                        'expectedUri' => '/foo/bar',
                    ],
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => 'fr',
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'forSameLocale' => false,
                        'expectedUri' => '/foo/bar-resolved',
                    ],
                ],
            ],
            'two mutually conflicting localized routes (for the same locale)' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => 'en',
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'forSameLocale' => false,
                        'expectedUri' => '/foo/bar',
                    ],
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => 'en',
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'forSameLocale' => false,
                        'expectedUri' => '/foo/bar',
                    ],
                ],
            ],
            'two mutually conflicting routes which conflict with a persisted route' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => null,
                        'existsInDatabase' => true,
                        'withSameContent' => false,
                        'forSameLocale' => false,
                        'expectedUri' => '/foo/bar-resolved',
                    ],
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => null,
                        'existsInDatabase' => true,
                        'withSameContent' => false,
                        'forSameLocale' => false,
                        'expectedUri' => '/foo/bar-resolved',
                    ],
                ],
            ],
            'two mutually conflicting localized routes which conflict with a persisted route' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => 'de',
                        'existsInDatabase' => true,
                        'withSameContent' => false,
                        'forSameLocale' => false,
                        'expectedUri' => '/foo/bar-resolved',
                    ],
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => 'en',
                        'existsInDatabase' => true,
                        'withSameContent' => false,
                        'forSameLocale' => false,
                        'expectedUri' => '/foo/bar-also-resolved',
                    ],
                ],
            ],
            'two mutually conflicting localized routes (for different locales) which conflict with a persisted route referencing the same content (for the same locale as the first route)' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => 'de',
                        'existsInDatabase' => true,
                        'withSameContent' => true,
                        'forSameLocale' => true,
                        'expectedUri' => '/foo/bar',
                    ],
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => 'en',
                        'existsInDatabase' => true,
                        'withSameContent' => true,
                        'forSameLocale' => false,
                        'expectedUri' => '/foo/bar-resolved',
                    ],
                ],
            ],
            'two mutually conflicting localized routes (for the same locale) which conflict with a persisted route (referencing the same content for the same locale)' => [
                [
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => 'de',
                        'existsInDatabase' => true,
                        'withSameContent' => true,
                        'forSameLocale' => true,
                        'expectedUri' => '/foo/bar',
                    ],
                    [
                        'generatedUri' => '/foo/bar',
                        'locale' => 'de',
                        'existsInDatabase' => true,
                        'withSameContent' => true,
                        'forSameLocale' => true,
                        'expectedUri' => '/foo/bar',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideBuildUriContextCollection
     */
    public function testBuildUriContextCollection(array $routes)
    {
        // Configure the stubs behavior regarding each route
        foreach ($routes as $index => $route) {
            $route = $this->populateRouteConfiguration($route, $routes);

            $this->configureUriGenerator($route);
            $this->configureAdapter($route);

            $routes[$index] = $route;
        }

        // Configure the stub behavior regarding the collection
        $this->configureCollectionBuilder($routes);

        // Run the tested method
        $this->manager->buildUriContextCollection($this->collection);

        // Expect manipulations on the contexts
        foreach ($routes as $index => $route) {
            $this->expectOnContext($route, $index);
        }

        // The defunct routes handler handles the defunct routes after the
        // processing of the contexts collection.
        // This should be done in a depending test. But PHPUnit does not
        // allow a depending test to receive the result of a test which use
        // a data provider.
        $this->defunctRouteHandler->handleDefunctRoutes($this->collection)->shouldBeCalled();
        $this->manager->handleDefunctRoutes();
    }

    /**
     * Custom implementation of the {@see \Prophecy\Argument::is()} token
     * shortcut.
     *
     * Fixes {@link https://github.com/phpspec/prophecy/issues/335}.
     */
    private static function is($value)
    {
        return Argument::that(function ($argument) use ($value) {
            return $value === $argument;
        });
    }

    /**
     * Populate the route configuration.
     *
     * The given route configuration is given:
     *  - the context,
     *  - the translated subject,
     *  - the created auto route,
     *  - the existing auto route within the database,
     *  - the existing route within the current routes collection.
     */
    private function populateRouteConfiguration(array $route, array $routes)
    {
        $translatedSubject = null;
        $createdAutoRoute = $this->prophesize(AutoRouteInterface::class)->reveal();
        $existingDatabaseAutoRoute = null;
        $existingCollectionRoute = null;

        $context = new UriContext(
            $this->collection,
            $route['generatedUri'],
            [],
            [],
            [],
            $route['locale']
        );

        if (null !== $route['locale']) {
            $translatedSubject = $this->getTranslatedSubjectForLocale($route['locale']);
        }

        if ($route['existsInDatabase']) {
            $existingDatabaseAutoRoute = $this->getDatabaseAutoRouteForUri($route['generatedUri']);
        }

        foreach ($routes as $otherRoute) {
            if ($otherRoute === $route) {
                break;
            }

            if ($otherRoute['expectedUri'] === $route['generatedUri']) {
                $existingCollectionRoute = $otherRoute;
                break;
            }
        }

        return array_merge($route, [
            'context' => $context,
            'subject' => $this->subject,
            'translatedSubject' => $translatedSubject,
            'createdAutoRoute' => $createdAutoRoute,
            'existingDatabaseAutoRoute' => $existingDatabaseAutoRoute,
            'existingCollectionRoute' => $existingCollectionRoute,
        ]);
    }

    /**
     * Configure the collection builder.
     *
     * The collection builder stub add a context to the collection for each
     * route configuration.
     */
    private function configureCollectionBuilder(array $routes)
    {
        $this->collectionBuilder->build(self::is($this->collection))->will(
            function ($args) use ($routes) {
                list($collection) = $args;

                foreach ($routes as $route) {
                    $collection->addUriContext($route['context']);
                }
            }
        );

        return $routes;
    }

    /**
     * Configure the URI generator.
     *
     * The URI generator stub:
     *  - generates the provided URI,
     *  - resolves a conflict.
     */
    private function configureUriGenerator(array $route)
    {
        $this->uriGenerator->generateUri(self::is($route['context']))
            ->willReturn($route['generatedUri']);

        $this->uriGenerator->resolveConflict(self::is($route['context']))
            ->willReturn($route['expectedUri']);
    }

    /**
     * Configure the adapter.
     *
     * The adapter stub:
     *  - generates the tag,
     *  - creates a new autoroute,
     *  - if the route exists in the database, finds it,
     *  - tells if the existing route matches the same content,
     *  - tells if the existing route matches the same locale,
     *  - if the route specify a locale, translates the content.
     */
    private function configureAdapter(array $route)
    {
        $this->adapter->generateAutoRouteTag(self::is($route['context']))
            ->willReturn($route['locale']);

        $this->adapter->createAutoRoute(self::is($route['context']), $route['locale'])
            ->willReturn($route['createdAutoRoute']);

        $this->adapter->findRouteForUri($route['generatedUri'], self::is($route['context']))
            ->willReturn($route['existingDatabaseAutoRoute']);

        if ($route['existsInDatabase']) {
            $this->adapter->compareAutoRouteContent($route['existingDatabaseAutoRoute'], $route['subject'])
                ->willReturn($route['withSameContent']);

            $this->adapter->compareAutoRouteLocale($route['existingDatabaseAutoRoute'], $route['locale'])
                ->willReturn($route['forSameLocale']);
        }

        if (null !== $route['existingCollectionRoute']) {
            $otherRoute = $route['existingCollectionRoute'];

            if ($otherRoute['existsInDatabase']) {
                $autoRoute = $otherRoute['existingDatabaseAutoRoute'];
            } else {
                $autoRoute = $otherRoute['createdAutoRoute'];
            }

            $this->adapter->compareAutoRouteContent($autoRoute, $route['subject'])
                ->willReturn($otherRoute['subject'] === $route['subject']);

            $this->adapter->compareAutoRouteLocale($autoRoute, $route['locale'])
                ->willReturn($otherRoute['locale'] === $route['locale']);
        }

        $this->adapter->translateObject($route['subject'], $route['locale'])
            ->willReturn($route['translatedSubject']);
    }

    /**
     * Expect the context status.
     *
     * The status is checked on the properties which can be modified by a setter.
     *
     * The context should contain:
     *  - an existing route if they match the same URI for the same content,
     *  - a new route otherwize,
     *  - a translated subject (which is the non translated subject if the route isn't localized),
     *  - the expected URI.
     */
    private function expectOnContext(array $route, $index)
    {
        $existingCollectionRoute = $route['existingCollectionRoute'];

        if ($route['existsInDatabase'] && $route['withSameContent'] && $route['forSameLocale']) {
            $expectedAutoRoute = $route['existingDatabaseAutoRoute'];
            $autoRouteError = 'The existing auto route from the database has not been reused';
        } elseif (null !== $existingCollectionRoute && $existingCollectionRoute['locale'] === $route['locale']) {
            if ($existingCollectionRoute['existsInDatabase']
                && $existingCollectionRoute['withSameContent']
                && $existingCollectionRoute['forSameLocale']) {
                $expectedAutoRoute = $existingCollectionRoute['existingDatabaseAutoRoute'];
            } else {
                $expectedAutoRoute = $existingCollectionRoute['createdAutoRoute'];
            }
            $autoRouteError = 'The existing auto route from the collection has not been reused';
        } else {
            $expectedAutoRoute = $route['createdAutoRoute'];
            $autoRouteError = 'A new auto route has not been created';
        }

        if (null !== $route['locale']) {
            $expectedTranslatedSubject = $route['translatedSubject'];
            $translatedSubjectError = 'The subject has not been translated';
        } else {
            $expectedTranslatedSubject = $route['subject'];
            $translatedSubjectError = 'The subject should not be translated';
        }

        if ($route['generatedUri'] === $route['expectedUri']) {
            $uriError = 'The generated URI has not been kept';
        } else {
            $uriError = 'The URI conflict has not been resolved';
        }

        $this->assertSame(
            $expectedTranslatedSubject,
            $route['context']->getTranslatedSubject(),
            sprintf('%s for the route #%d.', $translatedSubjectError, $index)
        );
        $this->assertSame(
            $expectedAutoRoute,
            $route['context']->getAutoRoute(),
            sprintf('%s for the route #%d.', $autoRouteError, $index)
        );
        $this->assertSame(
            $route['expectedUri'],
            $route['context']->getUri(),
            sprintf('%s for the route #%d.', $uriError, $index)
        );
    }

    /**
     * Give the auto route dummy which must be found within database using the
     * given URI.
     */
    private function getDatabaseAutoRouteForUri($uri)
    {
        if (isset($this->databaseAutoRoutes[$uri])) {
            $autoRoute = $this->databaseAutoRoutes[$uri];
        } else {
            $autoRoute = $this->prophesize(AutoRouteInterface::class)->reveal();

            $this->databaseAutoRoutes[$uri] = $autoRoute;
        }

        return $autoRoute;
    }

    /**
     * Give the translated subject for the given locale.
     */
    private function getTranslatedSubjectForLocale($locale)
    {
        if (isset($this->translatedSubjects[$locale])) {
            $translatedSubject = $this->translatedSubjects[$locale];
        } else {
            $translatedSubject = new \stdClass();

            $this->translatedSubjects[$locale] = $translatedSubject;
        }

        return $translatedSubject;
    }
}
