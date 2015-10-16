<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\ConflictResolver\Exception;

/**
 * Exception thrown when there is an existing URL and
 * the "ThrowException" conflict resolver is used.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ExistingUriException extends \Exception
{
}
