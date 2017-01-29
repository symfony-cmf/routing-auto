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

/**
 * Class which represents a URL and its associated locale.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class UriContext
{
    protected $subject;
    protected $translatedSubject;
    protected $locale;
    protected $uri;
    protected $autoRoute;
    protected $uriSchema;
    protected $tokenProviderConfigs = [];
    protected $conflictResolverConfig = [];
    protected $subjectMetadata;
    protected $defaults;

    public function __construct(
        $subject,
        $uriSchema,
        array $defaults,
        array $tokenProviderConfigs,
        array $conflictResolverConfig,
        $locale
    ) {
        $this->subject = $subject;
        $this->translatedSubject = $subject;
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
    public function getSubject()
    {
        return $this->subject;
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
