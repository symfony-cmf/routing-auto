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
use Symfony\Cmf\Component\RoutingAuto\AdapterInterface;

/**
 * This conflict resolver will generate candidate URLs by appending
 * a number to the URL. It will keep incrementing this number until
 * the URL does not exist.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoIncrementConflictResolver implements ConflictResolverInterface
{
    protected $adapter;
    protected $inc;

    /**
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
        $this->inc = 0;

        $uri = $uriContext->getUri();
        $candidateUri = $this->incrementUri($uri);

        while ($route = $this->adapter->findRouteForUri($candidateUri, $uriContext)) {
            $candidateUri = $this->incrementUri($uri);
        }

        return $candidateUri;
    }

    protected function incrementUri($uri)
    {
        return sprintf('%s-%s', $uri, ++$this->inc);
    }
}
