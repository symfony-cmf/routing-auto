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

/**
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface DefunctRouteHandlerInterface
{
    /**
     * Handle auto routes which refer to the given
     * document but which do not correspond to the URLs
     * generated.
     *
     * These routes are defunct - they are routes which
     * have used to be used to directly reference the
     * content, but which must now either be deleted
     * or perhaps replaced with a redirect route, or indeed
     * left alone to continue depending on the configuration.
     *
     * TODO
     */
    public function handleDefunctRoutes(UriContextCollection $uriContextCollection);
}
