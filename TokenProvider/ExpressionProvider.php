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

use Symfony\Cmf\Component\RoutingAuto\TokenProviderInterface;
use Symfony\Cmf\Bundle\CoreBundle\Slugifier\SlugifierInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Cmf\Component\RoutingAuto\UriContext;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ExpressionProvider implements TokenProviderInterface
{
    protected $slugifier;
    protected $language;

    public function __construct(ExpressionLanguage $language)
    {
        $this->language = $language;
    }

    /**
     * {@inheritDoc}
     */
    public function provideValue(UriContext $uriContext, $options)
    {
        $subject = $uriContext->getSubjectObject();
        $expression = $options['expression'];
        $evaluation = $this->language->evaluate($expression, array(
            'subject' => $subject
        ));

        return $evaluation;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolverInterface $optionsResolver)
    {
        $optionsResolver->setRequired(array(
            'expression',
        ));

        $optionsResolver->setAllowedTypes(array(
            'expression' => 'string',
        ));
    }
}
