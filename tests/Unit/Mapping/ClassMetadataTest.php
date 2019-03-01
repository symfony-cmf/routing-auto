<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\Mapping;

use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Component\RoutingAuto\Mapping\ClassMetadata;

class ClassMetadataTest extends TestCase
{
    public function testThrowExceptionNoDefinitionAtIndex()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No definition');

        $metadata = new ClassMetadata('stdClass');
        $metadata->getAutoRouteDefinition(123);
    }
}
