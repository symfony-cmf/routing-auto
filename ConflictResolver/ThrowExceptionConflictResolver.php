<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\ConflictResolver;

use Symfony\Cmf\Component\RoutingAuto\ConflictResolverInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContext;

/**
 * This conflcit resolver "resolves" conflicts by throwing exceptions.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ThrowExceptionConflictResolver implements ConflictResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolveConflict(UriContext $uriContext)
    {
        $uri = $uriContext->getUri();

        throw new Exception\ExistingUriException(sprintf(
            'There already exists an auto route for URL "%s" and the system is configured '.
            'to throw this exception in this case. Alternatively you can choose to use a '.
            'different strategy, for example, auto incrementation. Please refer to the '.
            'documentation for more information.',
            $uri
        ));
    }
}
