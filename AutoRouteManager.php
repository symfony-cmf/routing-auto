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

use Symfony\Cmf\Component\RoutingAuto\AdapterInterface;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;

/**
 * This class is concerned with the automatic creation of route objects.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRouteManager
{
    protected $adapter;
    protected $uriGenerator;
    protected $defunctRouteHandler;

    private $pendingUriContextCollections = array();

    /**
     * @param AdapterInterface             $adapter             Database adapter
     * @param UriGeneratorInterface        $uriGenerator        Routing auto URL generator
     * @param DefunctRouteHandlerInterface $defunctRouteHandler Handler for defunct routes
     */
    public function __construct(
        AdapterInterface $adapter,
        UriGeneratorInterface $uriGenerator,
        DefunctRouteHandlerInterface $defunctRouteHandler
    )
    {
        $this->adapter = $adapter;
        $this->uriGenerator = $uriGenerator;
        $this->defunctRouteHandler = $defunctRouteHandler;
    }

    /**
     * @param object $document
     */
    public function buildUriContextCollection(UriContextCollection $uriContextCollection)
    {
        $this->getUriContextsForDocument($uriContextCollection);

        foreach ($uriContextCollection->getUriContexts() as $uriContext) {
            $existingRoute = $this->adapter->findRouteForUri($uriContext->getUri());

            $autoRoute = null;

            if ($existingRoute) {
                $isSameContent = $this->adapter->compareAutoRouteContent($existingRoute, $uriContext->getSubjectObject());

                if ($isSameContent) {
                    $autoRoute = $existingRoute;
                    $autoRoute->setType(AutoRouteInterface::TYPE_PRIMARY);
                } else {
                    $uri = $uriContext->getUri();
                    $uri = $this->uriGenerator->resolveConflict($uriContext);
                    $uriContext->setUri($uri);
                }
            }

            if (!$autoRoute) {
                $autoRouteTag = $this->adapter->generateAutoRouteTag($uriContext);
                $autoRoute = $this->adapter->createAutoRoute($uriContext->getUri(), $uriContext->getSubjectObject(), $autoRouteTag);
            }

            $uriContext->setAutoRoute($autoRoute);
        }

        $this->pendingUriContextCollections[] = $uriContextCollection;
    }

    public function handleDefunctRoutes()
    {
        while ($uriContextCollection = array_pop($this->pendingUriContextCollections)) {
            $this->defunctRouteHandler->handleDefunctRoutes($uriContextCollection);
        }
    }

    /**
     * Populates an empty UriContextCollection with UriContexts
     *
     * @param $uriContextCollection UriContextCollection
     */
    private function getUriContextsForDocument(UriContextCollection $uriContextCollection)
    {
        $locales = $this->adapter->getLocales($uriContextCollection->getSubjectObject()) ? : array(null);

        foreach ($locales as $locale) {
            if (null !== $locale) {
                $this->adapter->translateObject($uriContextCollection->getSubjectObject(), $locale);
            }

            // create and add uri context to stack
            $uriContext = $uriContextCollection->createUriContext($locale);
            $uriContextCollection->addUriContext($uriContext);

            // generate the URL
            $uri = $this->uriGenerator->generateUri($uriContext);

            // update the context with the URL
            $uriContext->setUri($uri);
        }
    }
}
