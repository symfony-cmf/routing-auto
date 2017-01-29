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

use Symfony\Cmf\Component\RoutingAuto\Mapping\AutoRouteDefinition;
use Symfony\Cmf\Component\RoutingAuto\UriContextCollectionBuilder;

class UriContextCollectionBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->metadataFactory = $this->prophesize('Metadata\MetadataFactoryInterface');
        $this->adapter = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\AdapterInterface');

        $this->builder = new UriContextCollectionBuilder(
            $this->metadataFactory->reveal(),
            $this->adapter->reveal()
        );

        $this->subject = new \stdClass();
        $this->collection = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContextCollection');
        $this->metadata = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Mapping\ClassMetadata');
    }

    /**
     * It should populate the URI context collection with UriContext instances based on the required locales.
     */
    public function testBuild()
    {
        $locales = ['de', 'en'];
        $metadata = [
            'token_provider_configs' => [
                'foobar' => ['bar' => 'foo'],
            ],
            'conflict_resolver_config' => [
                'name' => 'foobar',
            ],
        ];
        $definitions = [
            'one' => new AutoRouteDefinition('/path/one', [
                '_type' => 'edit',
            ]),
            'two' => new AutoRouteDefinition('/path/to', [
                'uri_schema' => '/path/two',
                'defaults' => [
                    '_type' => 'view',
                ],
            ]),
        ];

        $this->collection->getSubject()->willReturn($this->subject);
        $this->adapter->getRealClassName('stdClass')->willReturn('STDCLASS');
        $this->metadataFactory->getMetadataForClass('STDCLASS')->willReturn($this->metadata->reveal());
        $this->adapter->getLocales($this->subject)->willReturn($locales);
        $this->metadata->getAutoRouteDefinitions()->willReturn($definitions);

        foreach ($locales as $locale) {
            $this->metadata->getTokenProviders()->willReturn($metadata['token_provider_configs']);
            $this->metadata->getConflictResolver()->wilLReturn($metadata['conflict_resolver_config']);

            $uriContext = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContext');

            foreach ($definitions as $definition) {
                $this->collection->createUriContext(
                    $definition->getUriSchema(),
                    $definition->getDefaults(),
                    $metadata['token_provider_configs'],
                    $metadata['conflict_resolver_config'],
                    $locale
                )->willReturn($uriContext->reveal());
                $this->collection->addUriContext($uriContext->reveal())->shouldBeCalled();
            }
        }

        $this->builder->build($this->collection->reveal());
    }
}
