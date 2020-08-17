<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\TokenProvider;

use Symfony\Cmf\Component\RoutingAuto\Tests\Unit\BaseTestCase;
use Symfony\Cmf\Component\RoutingAuto\TokenProvider\ContentMethodProvider;
use Symfony\Component\DependencyInjection\ExpressionLanguage;
use Symfony\Cmf\Component\RoutingAuto\TokenProvider\ExpressionProvider;

class ExpressionProviderTest extends BaseTestCase
{
    protected $slugifier;
    protected $article;
    protected $uriContext;

    public function setUp()
    {
        parent::setUp();

        $this->article = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\Tests\Resources\Fixtures\Article');
        $this->uriContext = $this->prophesize('Symfony\Cmf\Component\RoutingAuto\UriContext');
        $this->provider = new ExpressionProvider(new ExpressionLanguage());
    }

    public function provideGetValue()
    {
        return array(
            array(
                'hello',
                'subject.getTitle()',
                'hello',
            ),
        );
    }

    /**
     * @dataProvider provideGetValue
     */
    public function testGetValue($title, $expression, $expected)
    {
        $this->uriContext->getSubjectObject()->willReturn($this->article);
        $this->article->getTitle()->willReturn($title);

        $options = array(
            'expression' => $expression
        );

        $res = $this->provider->provideValue($this->uriContext->reveal(), $options);
        $this->assertEquals($expected, $res);
    }
}
