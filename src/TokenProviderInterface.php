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

namespace Symfony\Cmf\Component\RoutingAuto;

use Symfony\Component\OptionsResolver\OptionsResolver;

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
     * Configure the options for this token provider.
     *
     * @param OptionsResolverInterface $optionsResolver
     */
    public function configureOptions(OptionsResolver $optionsResolver);
}
