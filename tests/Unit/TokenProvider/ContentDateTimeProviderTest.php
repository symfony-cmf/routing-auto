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

use Symfony\Cmf\Component\RoutingAuto\TokenProvider\ContentDateTimeProvider;

class ContentDateTimeProviderTest extends \PHPUnit_Framework_TestCase
{
    protected $article;
    protected $uriContext;

    public function setUp()
    {
        $this->article = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\Article');
        $this->uriContext = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContext');
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
