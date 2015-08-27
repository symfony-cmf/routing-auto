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

use Symfony\Cmf\Component\RoutingAuto\Mapping\RouteMetadata;

/**
 * Class which represents a URL and its associated locale
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class UriContext
{
    protected $subjectObject;
    protected $locale;
    protected $uri;
    protected $autoRoute;
    protected $routeMetadata;

    public function __construct($subjectObject, $routeMetadata, $locale)
    {
        $this->subjectObject = $subjectObject;
        $this->locale = $locale;
        $this->routeMetadata = $routeMetadata;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    public function getRouteMetadata() 
    {
        return $this->routeMetadata;
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
