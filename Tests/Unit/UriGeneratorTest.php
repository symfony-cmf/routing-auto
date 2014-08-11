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

use Symfony\Cmf\Component\RoutingAuto\UriGenerator;
use Symfony\Cmf\Component\RoutingAuto\Tests\Unit\BaseTestCase;
use Prophecy\Argument;

class UriGeneratorTest extends BaseTestCase
{
    protected $driver;
    protected $serviceRegistry;
    protected $tokenProviders = array();
    protected $uriContext;

    public function setUp()
    {
        parent::setUp();

        $this->metadataFactory = $this->prophesize('Metadata\MetadataFactoryInterface');
        $this->metadata = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Mapping\ClassMetadata');
        $this->driver = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\AdapterInterface');
        $this->serviceRegistry = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\ServiceRegistry');
        $this->tokenProvider = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\TokenProviderInterface');
        $this->uriContext = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContext');

        $this->uriGenerator = new UriGenerator(
            $this->metadataFactory->reveal(),
            $this->driver->reveal(),
            $this->serviceRegistry->reveal()
        );
    }

    public function provideGenerateUri()
    {
        return array(
            array(
                '/this/is/{token_the_first}/a/uri',
                '/this/is/foobar_value/a/uri',
                array(
                    'token_the_first' => array(
                        'name' => 'foobar_provider',
                        'value' => 'foobar_value',
                        'options' => array(),
                    ),
                ),
            ),
            array(
                '/{this}/{is}/{token_the_first}/a/uri',
                '/that/was/foobar_value/a/uri',
                array(
                    'token_the_first' => array(
                        'name' => 'foobar_provider',
                        'value' => 'foobar_value',
                        'options' => array(),
                    ),
                    'this' => array(
                        'name' => 'barfoo_provider',
                        'value' => 'that',
                        'options' => array(),
                    ),
                    'is' => array(
                        'name' => 'dobar_provider',
                        'value' => 'was',
                        'options' => array(),
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider provideGenerateUri
     */
    public function testGenerateUri($uriSchema, $expectedUri, $tokenProviderConfigs)
    {
        $document = new \stdClass;
        $this->uriContext->getSubjectObject()->willReturn($document);
        $this->driver->getRealClassName('stdClass')
            ->willReturn('ThisIsMyStandardClass');

        $this->metadataFactory->getMetadataForClass('ThisIsMyStandardClass')
            ->willReturn($this->metadata);

        $this->metadata->getTokenProviders()
            ->willReturn($tokenProviderConfigs);

        $this->metadata->getUriSchema()
            ->willReturn($uriSchema);

        foreach ($tokenProviderConfigs as $tokenName => $tokenProviderConfig) {
            $providerName = $tokenProviderConfig['name'];

            $this->tokenProviders[$providerName] = $this->prophesize(
                'Symfony\Cmf\Component\RoutingAuto\TokenProviderInterface'
            );

            $this->serviceRegistry->getTokenProvider($tokenProviderConfig['name'])
                ->willReturn($this->tokenProviders[$providerName]);

            $this->tokenProviders[$providerName]->provideValue($this->uriContext, $tokenProviderConfig['options'])
                ->willReturn($tokenProviderConfig['value']);
            $this->tokenProviders[$providerName]->configureOptions(Argument::type('Symfony\Component\OptionsResolver\OptionsResolverInterface'))->shouldBeCalled();
        }

        $res = $this->uriGenerator->generateUri($this->uriContext->reveal());

        $this->assertEquals($expectedUri, $res);
    }
}
