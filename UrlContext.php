<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Component\RoutingAuto;

/**
 * Class which represents a URL and its associated locale
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class UrlContext
{
    protected $subjectObject;
    protected $locale;
    protected $url;
    protected $autoRoute;

    public function __construct($subjectObject, $locale)
    {
        $this->subjectObject = $subjectObject;
        $this->locale = $locale;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
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
