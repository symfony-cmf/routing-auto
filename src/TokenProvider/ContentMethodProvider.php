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

namespace Symfony\Cmf\Component\RoutingAuto\TokenProvider;

use Symfony\Cmf\Api\Slugifier\SlugifierInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
    public function configureOptions(OptionsResolver $optionsResolver)
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->setDefault('slugify', true);

        $optionsResolver->setAllowedTypes('slugify', 'bool');
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
}
