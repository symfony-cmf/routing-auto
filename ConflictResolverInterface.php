<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Component\RoutingAuto;

interface ConflictResolverInterface
{
    /**
     * If this method is called then the given URL is in
     * conflict with an existing URL and needs to be unconflicted.
     *
     * @param string $uri
     *
     * @return string unconflicted URL
     */
    public function resolveConflict(UriContext $uriContext);

}
