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

use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContext;
use Symfony\Cmf\Component\RoutingAuto\UriContextCollection;

class UriContextCollectionTest extends \PHPUnit_Framework_TestCase
{
    private $contextCollection;
    private $subject;

    public function setUp()
    {
        $this->subject = new \stdClass();
        $this->contextCollection = new UriContextCollection($this->subject);
    }

    public function testGetSetSubject()
    {
        $otherSubject = new \stdClass();

        $this->assertSame($this->subject, $this->contextCollection->getSubject());
        $this->contextCollection->setSubject($otherSubject);
        $this->assertSame($otherSubject, $this->contextCollection->getSubject());
    }

    public function testCreateUriContext()
    {
        $uriSchema = '/foo/{bar}/baz';
        $defaults = ['name' => 'value'];
        $tokenProvidersConfig = ['token'];
        $conflictResolverConfig = ['resolver'];
        $locale = 'fr';

        $rawContext = new UriContext(
            $this->contextCollection,
            $uriSchema,
            $defaults,
            $tokenProvidersConfig,
            $conflictResolverConfig,
            $locale
        );

        $createdContext = $this->contextCollection->createUriContext(
            $uriSchema,
            $defaults,
            $tokenProvidersConfig,
            $conflictResolverConfig,
            $locale
        );

        $this->assertEquals($rawContext, $createdContext);
    }

    public function testGetAddUriContexts()
    {
        $this->assertEmpty($this->contextCollection->getUriContexts());

        for ($count = 1; $count < 10; ++$count) {
            $this->contextCollection->addUriContext($this->prophesize(UriContext::class)->reveal());
            $this->assertCount($count, $this->contextCollection->getUriContexts());
        }
    }

    /**
     * Provide the contexts contained in the collection, the targeted auto
     * route and the expected result (`true` if the targeted auto route exists
     * in the collection, `false` otherwize).
     *
     * Notes:
     *  - an "empty" context does not contains any auto route,
     *  - the "matching" context is the one containing the targeted auto route.
     */
    public function provideContainsAutoRoute()
    {
        $targetedAutoRoute = $this->prophesize(AutoRouteInterface::class)->reveal();

        $matchingContext = $this->prophesize(UriContext::class);
        $matchingContext->getAutoRoute()
            ->willReturn($targetedAutoRoute);
        $matchingContext = $matchingContext->reveal();

        $notMatchingContext = $this->prophesize(UriContext::class);
        $notMatchingContext->getAutoRoute()
            ->willReturn($this->prophesize(AutoRouteInterface::class)->reveal());
        $notMatchingContext = $notMatchingContext->reveal();

        $emptyContext = $this->prophesize(UriContext::class);
        $emptyContext->getAutoRoute()
            ->willReturn(null);
        $emptyContext = $emptyContext->reveal();

        $collections = [
            'an empty collection' => [],
            'an empty context' => [$emptyContext],
            'a not matching context' => [$notMatchingContext],
            'the matching context' => [$matchingContext],
            'an empty and the matching contexts' => [
                $emptyContext,
                $matchingContext,
            ],
            'a not matching and the matching contexts' => [
                $notMatchingContext,
                $matchingContext,
            ],
            'an empty and a not matching contexts' => [
                $emptyContext,
                $notMatchingContext,
            ],
            'an empty, a not matching and the matching contexts' => [
                $emptyContext,
                $notMatchingContext,
                $matchingContext,
            ],
        ];

        $data = [];

        foreach ($collections as $description => $contexts) {
            $data[$description] = [
                $contexts,
                $targetedAutoRoute,
                in_array($matchingContext, $contexts, true),
            ];
        }

        return $data;
    }

    /**
     * @dataProvider provideContainsAutoRoute
     */
    public function testContainsAutoRoute(array $contexts, AutoRouteInterface $target, $shouldBeFound)
    {
        foreach ($contexts as $context) {
            $this->contextCollection->addUriContext($context);
        }

        $wasFound = $this->contextCollection->containsAutoRoute($target);

        $this->assertSame($shouldBeFound, $wasFound);
    }

    /**
     * Provide the contexts contained in the collection, the targeted URI and
     * the expected result (the matching auto route if it exists in the
     * collection, `null` otherwize).
     *
     * Notes:
     *  - an "empty" context does not contains any auto route,
     *  - the "matching" context is the one matching the targeted URI.
     */
    public function provideGetAutoRouteByUri()
    {
        $targetedUri = '/foo/bar';
        $otherUri = '/bar/baz';
        $targetedAutoRoute = $this->prophesize(AutoRouteInterface::class)->reveal();

        $matchingContext = $this->prophesize(UriContext::class);
        $matchingContext->getAutoRoute()
            ->willReturn($targetedAutoRoute);
        $matchingContext->getUri()
            ->willReturn($targetedUri);
        $matchingContext = $matchingContext->reveal();

        $notMatchingContext = $this->prophesize(UriContext::class);
        $notMatchingContext->getAutoRoute()
            ->willReturn($this->prophesize(AutoRouteInterface::class)->reveal());
        $notMatchingContext->getUri()
            ->willReturn($otherUri);
        $notMatchingContext = $notMatchingContext->reveal();

        $emptyMatchingContext = $this->prophesize(UriContext::class);
        $emptyMatchingContext->getAutoRoute()
            ->willReturn(null);
        $emptyMatchingContext->getUri()
            ->willReturn($targetedUri);
        $emptyMatchingContext = $emptyMatchingContext->reveal();

        $emptyNotMatchingContext = $this->prophesize(UriContext::class);
        $emptyNotMatchingContext->getAutoRoute()
            ->willReturn(null);
        $emptyNotMatchingContext->getUri()
            ->willReturn($otherUri);
        $emptyNotMatchingContext = $emptyNotMatchingContext->reveal();

        $collections = [
            'an empty collection' => [],
            'an empty not matching context' => [$emptyNotMatchingContext],
            'an empty matching context' => [$emptyMatchingContext],
            'a not matching context' => [$notMatchingContext],
            'the matching context' => [$matchingContext],
            'a not matching and a matching empty contexts' => [
                $emptyNotMatchingContext,
                $emptyMatchingContext,
            ],
            'an empty and a not empty not matching contexts' => [
                $emptyNotMatchingContext,
                $notMatchingContext,
            ],
            'an empty not matching and the matching contexts' => [
                $emptyNotMatchingContext,
                $matchingContext,
            ],
            'an empty matching and a not matching contexts' => [
                $emptyMatchingContext,
                $notMatchingContext,
            ],
            'an empty matching and the matching contexts' => [
                $emptyMatchingContext,
                $matchingContext,
            ],
            'a not matching and the matching contexts' => [
                $notMatchingContext,
                $matchingContext,
            ],
            'a not matching context, a not matching and a matching empty contexts' => [
                $emptyNotMatchingContext,
                $emptyMatchingContext,
                $notMatchingContext,
            ],
            'the matching context, a not matching and a matching empty contexts' => [
                $emptyNotMatchingContext,
                $emptyMatchingContext,
                $matchingContext,
            ],
            'the matching context, a not empty and an empty not matching contexts' => [
                $emptyNotMatchingContext,
                $notMatchingContext,
                $matchingContext,
            ],
            'an empty matching context, na not matching context and the matching context' => [
                $emptyMatchingContext,
                $notMatchingContext,
                $matchingContext,
            ],
            'a not matching and a matching empty contexts, a not matching context and the matching context' => [
                $emptyNotMatchingContext,
                $emptyMatchingContext,
                $notMatchingContext,
                $matchingContext,
            ],
        ];

        $data = [];

        foreach ($collections as $description => $contexts) {
            $data[$description] = [
                $contexts,
                $targetedUri,
                in_array($matchingContext, $contexts, true) ? $targetedAutoRoute : null,
            ];
        }

        return $data;
    }

    /**
     * @dataProvider provideGetAutoRouteByUri
     */
    public function testGetAutoRouteByUri(array $contexts, $target, $expectedAutoRoute)
    {
        foreach ($contexts as $context) {
            $this->contextCollection->addUriContext($context);
        }

        $foundAutoRoute = $this->contextCollection->getAutoRouteByUri($target);

        $this->assertSame($expectedAutoRoute, $foundAutoRoute);
    }

    public function provideGetAutoRouteByLocale()
    {
        $targetedLocale = 'fr';
        $otherLocale = 'de';

        $targetedAutoRoute = $this->prophesize(AutoRouteInterface::class);
        $targetedAutoRoute->getLocale()
            ->willReturn($targetedLocale);
        $targetedAutoRoute = $targetedAutoRoute->reveal();

        $matchingContext = $this->prophesize(UriContext::class);
        $matchingContext->getAutoRoute()
            ->willReturn($targetedAutoRoute);
        $matchingContext->getLocale()
            ->willReturn($targetedLocale);
        $matchingContext = $matchingContext->reveal();

        $notMatchingContext = $this->prophesize(UriContext::class);
        $notMatchingContext->getAutoRoute()
            ->willReturn($this->prophesize(AutoRouteInterface::class)->reveal());
        $notMatchingContext->getLocale()
            ->willReturn($otherLocale);
        $notMatchingContext = $notMatchingContext->reveal();

        $emptyMatchingContext = $this->prophesize(UriContext::class);
        $emptyMatchingContext->getAutoRoute()
            ->willReturn(null);
        $emptyMatchingContext->getLocale()
            ->willReturn($targetedLocale);
        $emptyMatchingContext = $emptyMatchingContext->reveal();

        $emptyNotMatchingContext = $this->prophesize(UriContext::class);
        $emptyNotMatchingContext->getAutoRoute()
            ->willReturn(null);
        $emptyNotMatchingContext->getLocale()
            ->willReturn($otherLocale);
        $emptyNotMatchingContext = $emptyNotMatchingContext->reveal();

        $collections = [
            'an empty collection' => [],
            'an empty not matching context' => [$emptyNotMatchingContext],
            'an empty matching context' => [$emptyMatchingContext],
            'a not matching context' => [$notMatchingContext],
            'the matching context' => [$matchingContext],
            'a not matching and a matching empty contexts' => [
                $emptyNotMatchingContext,
                $emptyMatchingContext,
            ],
            'an empty and a not empty not matching contexts' => [
                $emptyNotMatchingContext,
                $notMatchingContext,
            ],
            'an empty not matching and the matching contexts' => [
                $emptyNotMatchingContext,
                $matchingContext,
            ],
            'an empty matching and a not matching contexts' => [
                $emptyMatchingContext,
                $notMatchingContext,
            ],
            'an empty matching and the matching contexts' => [
                $emptyMatchingContext,
                $matchingContext,
            ],
            'a not matching and the matching contexts' => [
                $notMatchingContext,
                $matchingContext,
            ],
            'a not matching context, a not matching and a matching empty contexts' => [
                $emptyNotMatchingContext,
                $emptyMatchingContext,
                $notMatchingContext,
            ],
            'the matching context, a not matching and a matching empty contexts' => [
                $emptyNotMatchingContext,
                $emptyMatchingContext,
                $matchingContext,
            ],
            'the matching context, a not empty and an empty not matching contexts' => [
                $emptyNotMatchingContext,
                $notMatchingContext,
                $matchingContext,
            ],
            'an empty matching context, na not matching context and the matching context' => [
                $emptyMatchingContext,
                $notMatchingContext,
                $matchingContext,
            ],
            'a not matching and a matching empty contexts, a not matching context and the matching context' => [
                $emptyNotMatchingContext,
                $emptyMatchingContext,
                $notMatchingContext,
                $matchingContext,
            ],
        ];

        $data = [];

        foreach ($collections as $description => $contexts) {
            $data[$description] = [
                $contexts,
                $targetedLocale,
                in_array($matchingContext, $contexts, true) ? $targetedAutoRoute : null,
            ];
        }

        return $data;
    }

    /**
     * @dataProvider provideGetAutoRouteByLocale
     */
    public function testGetAutoRouteByLocale(array $contexts, $target, $expectedAutoRoute)
    {
        foreach ($contexts as $context) {
            $this->contextCollection->addUriContext($context);
        }

        $foundAutoRoute = $this->contextCollection->getAutoRouteByLocale($target);

        $this->assertSame($expectedAutoRoute, $foundAutoRoute);
    }
}
