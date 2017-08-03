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
 * Class which holds all the necessary information to make an auto route.
 *
 * The auto route will match the URI generated using the schema and the token
 * providers. It will contain the provided defaults and locale.
 *
 * If the generated URI conflicts with an existing one, the conflict resolver
 * will be in charge of resolving it.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class UriContext
{
    protected $collection;
    protected $translatedSubject;
    protected $locale;
    protected $uri;
    protected $autoRoute;
    protected $uriSchema;
    protected $tokenProviderConfigs;
    protected $conflictResolverConfig;
    protected $subjectMetadata;
    protected $defaults;

    /**
     * Construct the context.
     *
     * @param UriContextCollection $collection
     * @param string               $uriSchema
     * @param array                $defaults
     * @param array                $tokenProviderConfigs
     * @param array                $conflictResolverConfig
     * @param string               $locale
     */
    public function __construct(
        UriContextCollection $collection,
        $uriSchema,
        array $defaults,
        array $tokenProviderConfigs,
        array $conflictResolverConfig,
        $locale
    ) {
        $this->collection = $collection;
        $this->translatedSubject = $collection->getSubject();
        $this->uriSchema = $uriSchema;
        $this->defaults = $defaults;
        $this->tokenProviderConfigs = $tokenProviderConfigs;
        $this->conflictResolverConfig = $conflictResolverConfig;
        $this->locale = $locale;
    }

    /**
     * Return the collection this context belongs to.
     *
     * @return UriContextCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Return the URI the router must match.
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set the URI the router must match.
     *
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * Return the original version of the subject.
     *
     * @return object
     */
    public function getSubject()
    {
        return $this->collection->getSubject();
    }

    /**
     * Return the translated version of the subject object,
     * which *may* be a different object instance than the original subject.
     *
     * @return object
     */
    public function getTranslatedSubject()
    {
        return $this->translatedSubject;
    }

    /**
     * Set the translated subject.
     *
     * @param object $translatedSubject
     */
    public function setTranslatedSubject($translatedSubject)
    {
        $this->translatedSubject = $translatedSubject;
    }

    /**
     * Return the locale of the translated subject.
     *
     * Is null if the subject should not be translated.
     *
     * @return string|null
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Return the auto route matching this context.
     *
     * @return AutoRouteInterface
     */
    public function getAutoRoute()
    {
        return $this->autoRoute;
    }

    /**
     * Set the auto route matching this context.
     *
     * @param AutoRouteInterface $autoRoute
     */
    public function setAutoRoute($autoRoute)
    {
        $this->autoRoute = $autoRoute;
    }

    /**
     * Return the schema used to generate the URI.
     *
     * @return string
     */
    public function getUriSchema()
    {
        return $this->uriSchema;
    }

    /**
     * Return the configuration of the token providers used to generate the URI.
     *
     * @return array
     */
    public function getTokenProviderConfigs()
    {
        return $this->tokenProviderConfigs;
    }

    /**
     * Return the configuration of the conflict resolver.
     *
     * @return array
     */
    public function getConflictResolverConfig()
    {
        return $this->conflictResolverConfig;
    }

    /**
     * Return the defaults which must be set in the related auto route.
     *
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }
}
