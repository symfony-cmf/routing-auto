<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\TokenProvider;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Cmf\Component\RoutingAuto\UriContext;

class ContentDateTimeProvider extends BaseContentMethodProvider
{
    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver)
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setDefault('date_format', 'Y-m-d');

        $slugifyNormalizer = function ($options, $value) {
            if (null !== $value) {
                @trigger_error('The slugify option of '.__CLASS__.' is deprecated as of version 1.1 and will be removed in 2.0. Using it has no effect.', E_USER_DEPRECATED);
            }
        };

        $optionsResolver->setAllowedTypes('date_format', 'string');
        $optionsResolver->setDefined('slugify');
        $optionsResolver->setNormalizer('slugify', $slugifyNormalizer);
    }
}
