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

use Symfony\Cmf\Component\RoutingAuto\UriGenerator;
use Prophecy\Argument;

class UriGeneratorTest extends \PHPUnit_Framework_TestCase
{
    protected $driver;
    protected $serviceRegistry;
    protected $tokenProviders = array();
    protected $uriContext;

    public function setUp()
    {
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
            // tokens should be substituted with values from the token providers
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
            // tokens should be substituted with values from the token providers
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
            // an exception should be thrown if the token provider is not known
            array(
                '/this/is/{unknown_token}/life',
                null,
                array(),
                array('InvalidArgumentException', 'Unknown token "unknown_token"'),
            ),
            // an exception should be thrown if the generated URI is not absolute
            array(
                'this/is/not/absolute',
                null,
                array(),
                array('InvalidArgumentException', 'Generated non-absolute URI'),
            ),
            // no tokens need to be specified
            array(
                '/this/is/has/no/tokens',
                '/this/is/has/no/tokens',
                array(),
            ),
            // nothing should happen if allow_empty is true and the value is not empty
            array(
                '/{parent}/title',
                '/foobar_value/title',
                array(
                    'parent' => array(
                        'name' => 'foobar_provider',
                        'value' => 'foobar_value',
                        'options' => array(
                            'allow_empty' => true,
                        ),
                    ),
                ),
            ),
            // the empty token should be collapsed when allow_empty is true
            array(
                '/{parent}/title',
                '/title',
                array(
                    'parent' => array(
                        'name' => 'foobar_provider',
                        'value' => '',
                        'options' => array(
                            'allow_empty' => true,
                        ),
                    ),
                ),
            ),
            array(
                '/{parent_locator}/{title}',
                '/title',
                array(
                    'parent_locator' => array(
                        'name' => 'foobar_provider',
                        'value' => '',
                        'options' => array(
                            'allow_empty' => true,
                        ),
                    ),
                    'title' => array(
                        'name' => 'bar_provider',
                        'value' => 'title',
                        'options' => array(
                            'allow_empty' => true,
                        ),
                    ),
                ),
            ),
            // if the token value is a single "/" then it should be treated as an empty value and
            // any trailing slash should be collapsed.
            array(
                '{parent}/title',
                '/title',
                array(
                    'parent' => array(
                        'name' => 'foobar_provider',
                        'value' => '/',
                        'options' => array(
                            'allow_empty' => true,
                        ),
                    ),
                ),
            ),
            // if the last segment is empty and allow empty is true, then remove the leading slash
            array(
                '/{locale}/{parent}',
                '/de',
                array(
                    'locale' => array(
                        'name' => 'foobar_provider',
                        'value' => 'de',
                        'options' => array(
                            'allow_empty' => true,
                        ),
                    ),
                    'parent' => array(
                        'name' => 'barbar_provider',
                        'value' => '',
                        'options' => array(
                            'allow_empty' => true,
                        ),
                    ),
                ),
            ),
            // if the last segment is empty and has a trailing slash then the trailing slash should be
            // preserved
            array(
                '/{locale}/{parent}/',
                '/de/',
                array(
                    'locale' => array(
                        'name' => 'foobar_provider',
                        'value' => 'de',
                        'options' => array(
                            'allow_empty' => true,
                        ),
                    ),
                    'parent' => array(
                        'name' => 'barbar_provider',
                        'value' => '',
                        'options' => array(
                            'allow_empty' => true,
                        ),
                    ),
                ),
            ),
            // an exception should be thrown if a token is empty and allow_empty is false
            array(
                '/{parent}/title',
                '/title',
                array(
                    'parent' => array(
                        'name' => 'foobar_provider',
                        'value' => '',
                        'options' => array(
                            'allow_empty' => false,
                        ),
                    ),
                ),
                array(
                    'InvalidArgumentException', 'Token provider "foobar_provider" returned an empty value',
                ),
            ),
            // it should not throw a warning if the allow_empty option is absent and the value is empty.
            array(
                '/{parent}/title',
                '/title',
                array(
                    'parent' => array(
                        'name' => 'foobar_provider',
                        'value' => '',
                        'options' => array(),
                    ),
                ),
                array(
                    'InvalidArgumentException', 'Token provider "foobar_provider" returned an empty value',
                ),
            ),
        );
    }

    /**
     * @dataProvider provideGenerateUri
     */
    public function testGenerateUri($uriSchema, $expectedUri, $tokenProviderConfigs, $expectedException = null)
    {
        if ($expectedException) {
            list($exceptionType, $exceptionMessage) = $expectedException;
            $this->setExpectedException($exceptionType, $exceptionMessage);
        }

        $document = new \stdClass();
        $this->uriContext->getSubjectObject()->willReturn($document);
        $this->uriContext->getUri()->willReturn($uriSchema);
        $this->driver->getRealClassName('stdClass')
            ->willReturn('ThisIsMyStandardClass');

        $this->metadataFactory->getMetadataForClass('ThisIsMyStandardClass')
            ->willReturn($this->metadata);

        $this->metadata->getTokenProviders()
            ->willReturn($tokenProviderConfigs);

        $this->metadata->getUriSchema()
            ->willReturn($uriSchema);

        foreach ($tokenProviderConfigs as $tokenProviderConfig) {
            // set the defaults for the predictions
            $tokenProviderConfig['options'] = array_merge(array(
                'allow_empty' => false,
            ), $tokenProviderConfig['options']);

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
