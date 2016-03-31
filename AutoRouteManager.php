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

use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * This class is concerned with the automatic creation of route objects.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRouteManager
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var UriGeneratorInterface
     */
    protected $uriGenerator;

    /**
     * @var DefunctRouteHandlerInterface
     */
    protected $defunctRouteHandler;

    /**
     * @var UriContextCollection[]
     */
    private $pendingUriContextCollections = array();

    /**
     * @param AdapterInterface             $adapter             Database adapter
     * @param UriGeneratorInterface        $uriGenerator        Routing auto URL generator
     * @param DefunctRouteHandlerInterface $defunctRouteHandler Handler for defunct routes
     * @param EventDispatcher              $eventDispatcher     Dispatcher for events
     */
    public function __construct(
        AdapterInterface $adapter,
        UriGeneratorInterface $uriGenerator,
        DefunctRouteHandlerInterface $defunctRouteHandler
    ) {
        $this->adapter = $adapter;
        $this->uriGenerator = $uriGenerator;
        $this->defunctRouteHandler = $defunctRouteHandler;
    }

    /**
     * Build the URI context classes into the given UriContextCollection.
     *
     * @param UriContextCollection $uriContextCollection
     */
    public function buildUriContextCollection(UriContextCollection $uriContextCollection)
    {
        $this->getUriContextsForDocument($uriContextCollection);

        foreach ($uriContextCollection->getUriContexts() as $uriContext) {
            $existingRoute = $this->adapter->findRouteForUri($uriContext->getUri(), $uriContext);

            $autoRoute = null;

            if ($existingRoute) {
                $autoRoute = $this->handleExistingRoute($existingRoute, $uriContext);
            }

            if (!$autoRoute) {
                $autoRouteTag = $this->adapter->generateAutoRouteTag($uriContext);
                $autoRoute = $this->adapter->createAutoRoute($uriContext->getUri(), $uriContext->getSubjectObject(), $autoRouteTag);
            }

            $uriContext->setAutoRoute($autoRoute);
        }

        $this->pendingUriContextCollections[] = $uriContextCollection;
    }

    /**
     * Process defunct (no longer used) routes.
     */
    public function handleDefunctRoutes()
    {
        while ($uriContextCollection = array_pop($this->pendingUriContextCollections)) {
            $this->defunctRouteHandler->handleDefunctRoutes($uriContextCollection);
        }
    }

    /**
     * Handle the case where the generated path already exists.
     * Either if it does not reference the same content then we
     * have a conflict which needs to be resolved.
     *
     * @param Route      $route
     * @param UriContext $uriContext
     */
    private function handleExistingRoute($existingRoute, $uriContext)
    {
        $isSameContent = $this->adapter->compareAutoRouteContent($existingRoute, $uriContext->getSubjectObject());

        if ($isSameContent) {
            $autoRoute = $existingRoute;
            $autoRoute->setType(AutoRouteInterface::TYPE_PRIMARY);

            return $autoRoute;
        }

        $uri = $this->uriGenerator->resolveConflict($uriContext);
        $uriContext->setUri($uri);

        return;
    }

    /**
     * Populates an empty UriContextCollection with UriContexts.
     *
     * @param $uriContextCollection UriContextCollection
     */
    private function getUriContextsForDocument(UriContextCollection $uriContextCollection)
    {
        $locales = $this->adapter->getLocales($uriContextCollection->getSubjectObject()) ?: array(null);

        foreach ($locales as $locale) {
            if (null !== $locale) {
                $subjectObject = $this->adapter->translateObject($uriContextCollection->getSubjectObject(), $locale);

                if (null !== $subjectObject) {
                    $uriContextCollection->setSubjectObject($subjectObject);
                } else {
                    @trigger_error('AdapterInterface::translateObject() has to return the subjectObject as of version 1.1, support for by reference will be removed in 2.0.', E_USER_DEPRECATED);
                }
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
