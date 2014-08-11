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

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

interface TokenProviderInterface
{
    /**
     * Return a token value for the given configuration and
     * document.
     *
     * @param object $document
     * @param array  $options
     *
     * @return string
     */
    public function provideValue(UriContext $uriContext, $options);

    /**
     * Configure the options for this token provider
     *
     * @param OptionsResolverInterface $optionsResolver
     */
    public function configureOptions(OptionsResolverInterface $optionsResolver);
}
