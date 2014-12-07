<?php

namespace Symfony\Cmf\Component\RoutingAuto\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Cmf\Component\RoutingAuto\UriContext;

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class UriContextEvent extends Event
{
    private $uriContext;

    public function __construct(UriContext $uriContext)
    {
        $this->uriContext = $uriContext;
    }

    public function getUriContext()
    {
        return $this->uriContext;
    }
}
