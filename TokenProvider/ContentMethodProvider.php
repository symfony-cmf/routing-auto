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

use Symfony\Cmf\Api\Slugifier\SlugifierInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContext;

class ContentMethodProvider extends BaseContentMethodProvider
{
    protected $slugifier;

    public function __construct(SlugifierInterface $slugifier)
    {
        $this->slugifier = $slugifier;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeValue($value, UriContext $uriContext, $options)
    {
        if ($options['slugify']) {
            $value = $this->slugifier->slugify($value);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolverInterface $optionsResolver)
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setDefaults(array(
            'slugify' => true,
        ));

        $newApi = method_exists($optionsResolver, 'setDefined');

        if ($newApi) {
            $optionsResolver->setAllowedTypes('slugify', 'bool');
        } else {
            $optionsResolver->setAllowedTypes(array(
                'slugify' => 'bool',
            ));
        }
    }
}
