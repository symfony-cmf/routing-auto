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
use Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\Article;
use Symfony\Cmf\Component\RoutingAuto\TokenProvider\ContentDateTimeProvider;
use Symfony\Cmf\Component\RoutingAuto\UriContext;

class ContentDateTimeProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Article|ObjectProphecy
     */
    private $article;

    /**
     * @var UriContext|ObjectProphecy
     */
    private $uriContext;

    /**
     * @var ContentDateTimeProvider
     */
    private $provider;

    public function setUp()
    {
        $this->article = $this->prophesize(Article::class);
        $this->uriContext = $this->prophesize(UriContext::class);
        $this->provider = new ContentDateTimeProvider();
    }

    public function provideGetValue()
    {
        return [
            [
                [
                    'date_format' => 'Y-m-d',
                ],
                '2014-10-09',
            ],
            [
                [
                    'date_format' => 'Y/m/d',
                ],
                '2014/10/09',
            ],
        ];
    }

    /**
     * @dataProvider provideGetValue
     */
    public function testGetValue($options, $expectedResult)
    {
        $options = array_merge([
            'method' => 'getDate',
            'slugify' => true,
        ], $options);

        $this->uriContext->getSubject()->willReturn($this->article);
        $this->article->getDate()->willReturn(new \DateTime('2014-10-09'));

        $res = $this->provider->provideValue($this->uriContext->reveal(), $options);

        $this->assertEquals($expectedResult, $res);
    }
}
