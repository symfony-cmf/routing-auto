<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\TokenProvider;

use Symfony\Cmf\Component\RoutingAuto\TokenProviderInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentLocaleProvider implements TokenProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function provideValue(UriContext $uriContext, $options)
    {
        return $uriContext->getLocale();
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver)
    {
    }
}
