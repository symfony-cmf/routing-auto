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
     * @var UriContextCollectionBuilder
     */
    protected $collectionBuilder;

    /**
     * @param AdapterInterface             $adapter             Database adapter
     * @param UriGeneratorInterface        $uriGenerator        Routing auto URL generator
     * @param DefunctRouteHandlerInterface $defunctRouteHandler Handler for defunct routes
     * @param EventDispatcher              $eventDispatcher     Dispatcher for events
     */
    public function __construct(
        AdapterInterface $adapter,
        UriGeneratorInterface $uriGenerator,
        DefunctRouteHandlerInterface $defunctRouteHandler,
        UriContextCollectionBuilder $collectionBuilder
    ) {
        $this->adapter = $adapter;
        $this->uriGenerator = $uriGenerator;
        $this->defunctRouteHandler = $defunctRouteHandler;
        $this->collectionBuilder = $collectionBuilder;
    }

    /**
     * Build the URI context classes into the given UriContextCollection.
     *
     * @param UriContextCollection $uriContextCollection
     */
    public function buildUriContextCollection(UriContextCollection $uriContextCollection)
    {
        $this->collectionBuilder->build($uriContextCollection);

        foreach ($uriContextCollection->getUriContexts() as $uriContext) {
            $subject = $uriContextCollection->getSubjectObject();

            if (null !== $uriContext->getLocale()) {
                $translatedSubject = $this->adapter->translateObject($subject, $uriContext->getLocale());

                if (null === $translatedSubject) {
                    @trigger_error('AdapterInterface::translateObject() has to return the subject as of version 1.1, support for by reference will be removed in 2.0.', E_USER_DEPRECATED);
                } else {
                    if ($translatedSubject !== $subject) {
                        $uriContext->setTranslatedSubjectObject($translatedSubject);
                    }
                }
            }

            // generate the URI
            $uri = $this->uriGenerator->generateUri($uriContext);
            $uriContext->setUri($uri);
            $existingRoute = $this->adapter->findRouteForUri($uri, $uriContext);

            // handle existing route
            $autoRoute = null;
            if ($existingRoute) {
                $autoRoute = $this->handleExistingRoute($existingRoute, $uriContext);
            }

            // handle new route
            if (null === $autoRoute) {
                $autoRouteTag = $this->adapter->generateAutoRouteTag($uriContext);

                // TODO: The second argument below is now **pointless**, as the
                // UriContext contains both the original and translated subject
                // objects.
                //
                // See: https://github.com/symfony-cmf/RoutingAuto/issues/73
                $autoRoute = $this->adapter->createAutoRoute($uriContext, $subject, $autoRouteTag);
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
    }
}
