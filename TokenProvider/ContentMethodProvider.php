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

use Symfony\Cmf\Bundle\CoreBundle\Slugifier\SlugifierInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContentMethodProvider extends BaseContentMethodProvider
{
    protected $slugifier;

    public function __construct(SlugifierInterface $slugifier)
    {
        $this->slugifier = $slugifier;
    }

    /**
     * {@inheritDoc}
     */
    protected function normalizeValue($value, UriContext $uriContext, $options)
    {
        if ($options['slugify']) {
            $value = $this->slugifier->slugify($value);
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolverInterface $optionsResolver)
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setDefaults(array(
            'slugify' => true,
        ));

        $optionsResolver->setAllowedTypes(array(
            'slugify' => 'bool',
        ));
    }
}
