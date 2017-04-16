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

use Symfony\Cmf\Component\RoutingAuto\UriContext;
use Symfony\Cmf\Component\RoutingAuto\TokenProviderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class BaseContentMethodProvider implements TokenProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function provideValue(UriContext $uriContext, $options)
    {
        $object = $uriContext->getSubjectObject();
        $method = $options['method'];
        $this->checkMethodExists($object, $method);

        return $this->normalizeValue($object->$method(), $uriContext, $options);
    }

    protected function checkMethodExists($object, $method)
    {
        if (!method_exists($object, $method)) {
            throw new \InvalidArgumentException(sprintf(
                'Method "%s" does not exist on object "%s"',
                $method,
                get_class($object)
            ));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolverInterface $optionsResolver)
    {
        $optionsResolver->setRequired(array('method'));
    }

    /**
     * Normalizes the value (e.g. slugifying).
     *
     * It can also throw exceptions when the value is not in a correct format.
     *
     * @param string     $value
     * @param UriContext $uriContext
     * @param array      $options
     *
     * @return string The normalized value
     */
    abstract protected function normalizeValue($value, UriContext $uriContext, $options);
}
