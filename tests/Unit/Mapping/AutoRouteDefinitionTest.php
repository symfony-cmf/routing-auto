<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\Mapping;

use Symfony\Cmf\Component\RoutingAuto\Mapping\AutoRouteDefinition;

class AutoRouteDefinitionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Its should merge and replace its parent token in the URI schema with the
     * token of the incoming definition.
     */
    public function testMergeUri()
    {
        $childDefinition = $this->createDefinition('/test{parent}');

        $definition = $this->createDefinition('/tset');
        $definition->merge($childDefinition);

        $this->assertEquals('/test/tset', $definition->getUriSchema());
    }

    /**
     * It should merge defaults.
     */
    public function testMergeDefaults()
    {
        $childDefinition = $this->createDefinition('/test', [
            'one' => 'two',
            'three' => 'four',
        ]);
        $parentDefinition = $this->createDefinition('/test', [
            'one' => 'seven',
            'three' => 'nine',
            'six' => 'seven',
        ]);

        $parentDefinition->merge($childDefinition);

        $this->assertEquals([
            'one' => 'two',
            'three' => 'four',
            'six' => 'seven',
        ], $parentDefinition->getDefaults());
    }

    private function createDefinition($uriSchema, array $defaults = [])
    {
        return new AutoRouteDefinition($uriSchema, $defaults);
    }
}
