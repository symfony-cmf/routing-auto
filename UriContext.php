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
    protected $locale;
    protected $uri;
    protected $autoRoute;

    public function __construct($subjectObject, $locale)
    {
        $this->subjectObject = $subjectObject;
        $this->locale = $locale;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    public function getSubjectObject()
    {
        return $this->subjectObject;
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
}
