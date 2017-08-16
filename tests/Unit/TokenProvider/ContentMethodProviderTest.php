<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\TokenProvider;

use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Cmf\Api\Slugifier\SlugifierInterface;
use Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\Article;
use Symfony\Cmf\Component\RoutingAuto\TokenProvider\ContentMethodProvider;
use Symfony\Cmf\Component\RoutingAuto\UriContext;

class ContentMethodProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SlugifierInterface|ObjectProphecy
     */
    private $slugifier;

    /**
     * @var Article|ObjectProphecy
     */
    private $article;

    /**
     * @var UriContext|ObjectProphecy
     */
    private $uriContext;

    /**
     * @var ContentMethodProvider
     */
    private $provider;

    public function setUp()
    {
        $this->slugifier = $this->prophesize(SlugifierInterface::class);
        $this->article = $this->prophesize(Article::class);
        $this->uriContext = $this->prophesize(UriContext::class);
        $this->provider = new ContentMethodProvider($this->slugifier->reveal());
    }

    public function provideGetValue()
    {
        return [
            [
                [
                    'method' => 'getTitle',
                    'slugify' => true,
                ],
                true,
            ],
            [
                [
                    'method' => 'getTitle',
                    'slugify' => false,
                ],
                true,
            ],
            [
                [
                    'method' => 'getMethodNotExist',
                    'slugify' => false,
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider provideGetValue
     */
    public function testGetValue($options, $methodExists = false)
    {
        $method = $options['method'];
        $this->uriContext->getSubject()->willReturn($this->article);

        if (!$methodExists) {
            $this->setExpectedException(
                'InvalidArgumentException', 'Method "'.$options['method'].'" does not exist'
            );
        } else {
            $expectedResult = 'This is value';
            $this->article->$method()->willReturn($expectedResult);
        }

        if ($options['slugify']) {
            $expectedResult = 'this-is-value';
            $this->slugifier->slugify('This is value')->willReturn($expectedResult);
        }

        $res = $this->provider->provideValue($this->uriContext->reveal(), $options);

        $this->assertEquals($expectedResult, $res);
    }
}
