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
 * This interface should be implemented by classes which
 * need to configure options.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
interface ConfigurableInterface
{
    /**
     * Configure the options for this token provider
     *
     * @param OptionsResolverInterface $optionsResolver
     */
    public function configureOptions(OptionsResolverInterface $optionsResolver);
}
