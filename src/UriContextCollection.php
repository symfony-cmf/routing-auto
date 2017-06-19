<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto;

use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;

/**
 * Class which holds a collection of URI contexts related to the same subject.
 */
class UriContextCollection
{
    protected $subject;
    protected $uriContexts = [];

    /**
     * Construct the collection for the given subject.
     *
     * @param object $subject
     */
    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Set the subject this collection is related to.
     *
     * @param object $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Return the "subject" of this URI context, i.e. the object
     * for which an auto route is required.
     *
     * @return object
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Create a URI context.
     *
     * The context is created for this collection with the given URI schema,
     * route defaults, token provider configuration, conflict resolver
     * configuration and locale.
     *
     * @param string $uriSchema
     * @param array  $defaults
     * @param array  $tokenProviderConfigs
     * @param array  $conflictResolverConfigs
     * @param string $locale
     *
     * @return UriContext
     */
    public function createUriContext(
        $uriSchema,
        array $defaults,
        array $tokenProviderConfigs,
        array $conflictResolverConfigs,
        $locale
    ) {
        $uriContext = new UriContext(
            $this,
            $uriSchema,
            $defaults,
            $tokenProviderConfigs,
            $conflictResolverConfigs,
            $locale
        );

        return $uriContext;
    }

    /**
     * Push a URL context onto the stack.
     *
     * @param UriContext $uriContext
     */
    public function addUriContext(UriContext $uriContext)
    {
        $this->uriContexts[] = $uriContext;
    }

    /**
     * Return the URI contexts contained in the stack.
     *
     * @return array
     */
    public function getUriContexts()
    {
        return $this->uriContexts;
    }

    /**
     * Check if any of the UriContexts in the stack contain
     * the given auto route.
     *
     * @param AutoRouteInterface $autoRoute
     *
     * @return bool
     */
    public function containsAutoRoute(AutoRouteInterface $autoRoute)
    {
        foreach ($this->uriContexts as $uriContext) {
            if ($autoRoute === $uriContext->getAutoRoute()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get an auto route by its URI.
     *
     * @param string $uri
     *
     * @return AutoRouteInterface|null
     */
    public function getAutoRouteByUri($uri)
    {
        foreach ($this->uriContexts as $uriContext) {
            $autoRoute = $uriContext->getAutoRoute();

            if (null !== $autoRoute && $uri === $uriContext->getUri()) {
                return $autoRoute;
            }
        }

        return null;
    }

    /**
     * Get an auto route by its locale.
     *
     * @param string $locale
     *
     * @return AutoRouteInterface|null
     */
    public function getAutoRouteByLocale($locale)
    {
        foreach ($this->uriContexts as $uriContext) {
            $autoRoute = $uriContext->getAutoRoute();

            if (null !== $autoRoute && $locale === $uriContext->getLocale()) {
                return $autoRoute;
            }
        }

        return null;
    }
}
