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
     *  - withSameContent (boolean): is the existing route referring to the same content,
     *  - expectedUri (string): the URI expected to be set on the current route.
     *
     * If the generated URI and the expected one are different, it means that
     * the conflict resolver should be called by the manager.
     */
    public function provideBuildUriContextCollection()
    {
        return [
            'a single route' => [
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
            'a single localized route' => [
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
            'a single route conflicting with a persisted one' => [
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
            'a single route conflicting with a persisted one referencing the same content' => [
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
            'a single localized route conflicting with a persisted one' => [
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
            'a single localized route conflicting with a persisted one referencing the same content' => [
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
            'a single localized route conflicting with a persisted one referencing the same content for the same locale' => [
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
            'two routes where the second one conflicts with the first one' => [
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
            'two localized routes where the second one conflicts with the first one' => [
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
            'two localized routes where the second one conflicts with the first one for the same locale' => [
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
            'two routes where the second one conflicts with the first one which conflicts with a persisted route' => [
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
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'forSameLocale' => false,
                        'expectedUri' => '/foo/bar-also-resolved',
                    ],
                ],
            ],
            'two localized routes where the second one conflicts with the first one which conflicts with a persisted route' => [
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
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'forSameLocale' => false,
                        'expectedUri' => '/foo/bar-also-resolved',
                    ],
                ],
            ],
            'two localized routes where the second one conflicts with the first one which conflicts with a persisted route (referencing the same content for the same locale)' => [
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
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'forSameLocale' => false,
                        'expectedUri' => '/foo/bar-resolved',
                    ],
                ],
            ],
            'two localized routes where the second one conflicts with the first one (for the same locale) which conflicts with a persisted route (referencing the same content for the same locale)' => [
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
                        'existsInDatabase' => false,
                        'withSameContent' => false,
                        'forSameLocale' => false,
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
        $collection = new UriContextCollection(new \stdClass());

        // Configure the stubs behavior regarding each route
        foreach ($routes as $i => $route) {
            $context = $collection->createUriContext(
                $route['generatedUri'],
                [],
                [],
                [],
                $route['locale']
            );

            $route['subject'] = $context->getSubject();

            $this->configureUriGenerator($context, $route);
            $this->configureAdapter($context, $route);

            $route['context'] = $context;
            $routes[$i] = $route;

            $collection->addUriContext($context);
        }

        // Run the tested method
        $this->manager->buildUriContextCollection($collection);

        // Expect manipulations on the contexts
        foreach ($routes as $route) {
            $this->expectOnContext($route['context'], $route);
        }

        // The defunct routes handler handles the defunct routes after the
        // processing of the contexts collection.
        // This should be done in a depending test. But PHPUnit does not
        // allow a depending test to receive the result of a test which use
        // a data provider.
        $this->defunctRouteHandler->handleDefunctRoutes($collection)->shouldBeCalled();
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
     * Configure the URI generator.
     *
     * The URI generator stub:
     *  - generates the provided URI,
     *  - resolves a conflict.
     */
    private function configureUriGenerator(UriContext $context, array $route)
    {
        $this->uriGenerator->generateUri(self::is($context))
            ->willReturn($route['generatedUri']);

        $this->uriGenerator->resolveConflict(self::is($context))->willReturn($route['expectedUri']);
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
    private function configureAdapter(UriContext $context, array $route)
    {
        $tag = 'tag';
        $foundRoute = null;
        $translatedSubject = null;

        if ($route['existsInDatabase']) {
            $foundRoute = $this->prophesize(AutoRouteInterface::class);
        }

        if (!is_null($route['locale'])) {
            $translatedSubject = new \stdClass();
        }

        $this->adapter->generateAutoRouteTag(self::is($context))->willReturn($tag);

        $this->adapter->createAutoRoute(self::is($context), $route['subject'], $tag)
            ->willReturn($this->prophesize(AutoRouteInterface::class));

        $this->adapter->findRouteForUri($route['generatedUri'], self::is($context))->willReturn($foundRoute);

        if (!is_null($foundRoute)) {
            $this->adapter->compareAutoRouteContent($foundRoute->reveal(), $route['subject'])
                ->willReturn($route['withSameContent']);

            $this->adapter->compareAutoRouteLocale($foundRoute->reveal(), $route['locale'])
                ->willReturn($route['forSameLocale']);
        }

        $this->adapter->translateObject($route['subject'], $route['locale'])->willReturn($translatedSubject);
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
    private function expectOnContext(UriContext $context, array $route)
    {
        $translatedSubject = $route['subject'];
        $translatedSubjectError = 'The translated subject must be the non translated one.';

        if ($route['existsInDatabase'] and $route['withSameContent']) {
            $expectedAutoRoute = $this->adapter->reveal()->findRouteForUri(
                $route['generatedUri'],
                $context
            );

            $autoRouteError = 'The existing auto route has not been reused.';
        } else {
            $tag = $this->adapter->reveal()->generateAutoRouteTag($context);
            $expectedAutoRoute = $this->adapter->reveal()->createAutoRoute(
                $context,
                $route['subject'],
                $tag
            );

            $autoRouteError = 'A new auto route has not been created.';
        }

        if (!is_null($route['locale'])) {
            $translatedSubject = $this->adapter->reveal()->translateObject(
                $route['subject'],
                $route['locale']
            );

            $translatedSubjectError = 'The subject has not been translated.';
        }

        $this->assertSame(
            $translatedSubject,
            $context->getTranslatedSubject(),
            $translatedSubjectError
        );
        $this->assertSame(
            $expectedAutoRoute,
            $context->getAutoRoute(),
            $autoRouteError
        );
        $this->assertSame(
            $route['expectedUri'],
            $context->getUri(),
            'The context does not contain the expected URI.'
        );
    }
}
