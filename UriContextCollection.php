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

use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;

class UriContextCollection
{
    protected $subjectObject;
    protected $uriContexts = array();

    /**
     * @param mixed $subjectObject Subject for URL generation
     */
    public function __construct($subjectObject)
    {
        $this->subjectObject = $subjectObject;
    }

    /**
     * Return the "subject" of this URL context, i.e. the object
     * for which an auto route is required.
     *
     * @return object
     */
    public function getSubjectObject()
    {
        return $this->subjectObject;
    }

    /**
     * Create and a URL context
     *
     * @param string $uri    URL
     * @param string $locale Locale for given URL
     *
     * @return UriContext
     */
    public function createUriContext($locale)
    {
        $uriContext = new UriContext(
            $this->getSubjectObject(),
            $locale
        );

        return $uriContext;
    }

    /**
     * Push a URL context onto the stack
     *
     * @param UriContext $uriContext
     */
    public function addUriContext(UriContext $uriContext)
    {
        $this->uriContexts[] = $uriContext;
    }

    public function getUriContexts()
    {
        return $this->uriContexts;
    }

    /**
     * Return true if any one of the UriContexts in the stacj
     * contain the given auto route
     *
     * @param AutoRouteInterface $autoRoute
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

    public function getAutoRouteByTag($tag)
    {
        foreach ($this->uriContexts as $uriContext) {
            $autoRoute = $uriContext->getAutoRoute();
            if ($tag === $autoRoute->getAutoRouteTag()) {
                return $autoRoute;
            }
        }

        return null;
    }
}
