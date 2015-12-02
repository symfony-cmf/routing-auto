<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto;

/**
 * Class which represents a URL and its associated locale.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class UriContext
{
    protected $subjectObject;
    protected $translatedSubject;
    protected $locale;
    protected $uri;
    protected $autoRoute;
    protected $uriSchema;
    protected $tokenProviderConfigs = array();
    protected $conflictResolverConfig = array();
    protected $subjectMetadata;
    protected $defaults;

    public function __construct(
        $subjectObject,
        $uriSchema,
        array $defaults,
        array $tokenProviderConfigs,
        array $conflictResolverConfig,
        $locale
    ) {
        $this->subjectObject = $subjectObject;
        $this->translatedSubject = $subjectObject;
        $this->locale = $locale;
        $this->uriSchema = $uriSchema;
        $this->tokenProviderConfigs = $tokenProviderConfigs;
        $this->conflictResolverConfig = $conflictResolverConfig;
        $this->defaults = $defaults;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * Return the original version of the subject.
     *
     * @return object
     */
    public function getSubjectObject()
    {
        return $this->subjectObject;
    }

    /**
     * Return the translated version of the subject object,
     * which *may* be a different object instance than the original subject.
     *
     * @return object
     */
    public function getTranslatedSubjectObject()
    {
        return $this->translatedSubject;
    }

    /**
     * Set the translated subject.
     *
     * @param object $translatedSubject
     */
    public function setTranslatedSubjectObject($translatedSubject)
    {
        $this->translatedSubject = $translatedSubject;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function getAutoRoute()
    {
        return $this->autoRoute;
    }

    public function setAutoRoute($autoRoute)
    {
        $this->autoRoute = $autoRoute;
    }

    public function getUriSchema()
    {
        return $this->uriSchema;
    }

    public function getTokenProviderConfigs()
    {
        return $this->tokenProviderConfigs;
    }

    public function getConflictResolverConfig()
    {
        return $this->conflictResolverConfig;
    }

    public function getDefaults()
    {
        return $this->defaults;
    }
}
