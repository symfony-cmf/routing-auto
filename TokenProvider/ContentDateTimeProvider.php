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

class ContentDateTimeProvider extends ContentMethodProvider
{
    /**
     * {@inheritDoc}
     */
    public function provideValue(UriContext $uriContext, $options)
    {
        $object = $uriContext->getSubjectObject();
        $method = $options['method'];
        $this->checkMethodExists($object, $method);

        $date = $object->$method();

        if (!$date instanceof \DateTime) {
            throw new \RuntimeException(sprintf('Method %s:%s must return an instance of DateTime.',
                get_class($object),
                $method
            ));
        }

        $string = $date->format($options['date_format']);

        return $string;
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
    }
}
