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

use Symfony\Cmf\Component\RoutingAuto\UriContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentDateTimeProvider extends BaseContentMethodProvider
{
    /**
     * {@inheritdoc}
     */
    protected function normalizeValue($date, UriContext $uriContext, $options)
    {
        if (!$date instanceof \DateTime) {
            throw new \RuntimeException(sprintf('Method %s:%s must return an instance of DateTime.',
                get_class($uriContext->getSubject()),
                $options['method']
            ));
        }

        return $date->format($options['date_format']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver)
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setDefault('date_format', 'Y-m-d');

        $optionsResolver->setAllowedTypes('date_format', 'string');
    }
}
