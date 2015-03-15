<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Component\RoutingAuto\TokenProvider;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContext;

class ContentDateTimeProvider extends BaseContentMethodProvider
{
    /**
     * {@inheritDoc}
     */
    protected function normalizeValue($date, UriContext $uriContext, $options)
    {
        if (!$date instanceof \DateTime) {
            throw new \RuntimeException(sprintf('Method %s:%s must return an instance of DateTime.',
                get_class($uriContext->getSubjectObject()),
                $options['method']
            ));
        }

        return $date->format($options['date_format']);
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolverInterface $optionsResolver)
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setRequired(array(
            'date_format',
        ));

        $optionsResolver->setDefaults(array(
            'date_format' => 'Y-m-d',
        ));

        $optionsResolver->setAllowedTypes(array(
            'date_format' => 'string',
        ));
        
        // The slugify option is deprecated as of version 1.1 and will be removed in 2.0. Setting it doesn't change anything
        if (!method_exists($optionsResolver, 'setDefined')) {
            // To support Symfony <2.6
            $optionsResolver->setOptional(array('slugify'));
        } else {
            $optionsResolver->setDefined(array('slugify'));
        }
    }
}
