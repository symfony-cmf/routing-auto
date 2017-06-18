<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto;

/**
 * Interface for class which handles URL generation and conflict resolution.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface UriGeneratorInterface
{
    /**
     * Generate a URL for the given document.
     *
     * @return string
     */
    public function generateUri(UriContext $uriContext);

    /**
     * The given URL already exists in the database or the given contexts
     * collection, this method should delegate the task of resolving the
     * conflict to the ConflictResolver configured in the mapping for the
     * document.
     *
     * @return string
     */
    public function resolveConflict(UriContext $uriContext);
}
