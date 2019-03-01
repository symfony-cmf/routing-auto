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

namespace Symfony\Cmf\Component\RoutingAuto\ConflictResolver;

use Symfony\Cmf\Component\RoutingAuto\AdapterInterface;
use Symfony\Cmf\Component\RoutingAuto\ConflictResolverInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContext;

/**
 * This conflict resolver will generate candidate URLs by appending
 * a number to the URL. It will keep incrementing this number until
 * the URL does not exist.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoIncrementConflictResolver implements ConflictResolverInterface
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * To count the increment, increased until conflic is resolved.
     *
     * @var int
     */
    private $index;

    /**
     * Construct the conflict resolver using the given adapter.
     *
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveConflict(UriContext $uriContext)
    {
        $this->index = 0;
        $candidateUri = $uri = $uriContext->getUri();

        while ($this->isUriConflicting($candidateUri, $uriContext)) {
            $candidateUri = $this->incrementUri($uri);
        }

        return $candidateUri;
    }

    /**
     * Tell if the given URI for the given context is conflicting with another
     * route.
     */
    private function isUriConflicting($uri, UriContext $uriContext)
    {
        return null !== $uriContext->getCollection()->getAutoRouteByUri($uri)
            || null !== $this->adapter->findRouteForUri($uri, $uriContext);
    }

    private function incrementUri($uri)
    {
        return sprintf('%s-%s', $uri, ++$this->index);
    }
}
