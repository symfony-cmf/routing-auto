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
    private $pendingUriContextCollections = [];

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
            $subject = $uriContextCollection->getSubject();

            if (null !== $uriContext->getLocale()) {
                $translatedSubject = $this->adapter->translateObject($subject, $uriContext->getLocale());

                if ($translatedSubject !== $subject) {
                    $uriContext->setTranslatedSubject($translatedSubject);
                }
            }

            // generate the URI
            $uri = $this->uriGenerator->generateUri($uriContext);
            $uriContext->setUri($uri);
            $existingRoute = $this->findExistingRoute($uriContext, $uriContextCollection);

            // handle existing route
            $autoRoute = null;
            if ($existingRoute) {
                $autoRoute = $this->handleExistingRoute($existingRoute, $uriContext, $uriContextCollection);
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
     * Find an existing route which matches the URI of the given context.
     *
     * It is searched within the currently processed collection and the already
     * persisted routes (using the adapter).
     */
    private function findExistingRoute(
        UriContext $uriContext,
        UriContextCollection $uriContextCollection
    ) {
        $uri = $uriContext->getUri();
        $existingRoute = null;

        // As the auto route is put in the context after the conflict has been
        // resolved, we don't need to check if the found auto route is the one
        // contained in the given context.
        $existingRoute = $uriContextCollection->getAutoRouteByUri($uri);

        if (is_null($existingRoute)) {
            $existingRoute = $this->adapter->findRouteForUri($uri, $uriContext);
        }

        return $existingRoute;
    }

    /**
     * Handle the case where the generated path already exists.
     * Either if it does not reference the same content then we
     * have a conflict which needs to be resolved.
     */
    private function handleExistingRoute(
        AutoRouteInterface $existingRoute,
        UriContext $uriContext,
        UriContextCollection $uriContextCollection
    ) {
        $isSameContent = $this->adapter->compareAutoRouteContent($existingRoute, $uriContext->getSubject());
        $isSameLocale = $this->adapter->compareAutoRouteLocale($existingRoute, $uriContext->getLocale());

        if ($isSameContent && $isSameLocale) {
            $autoRoute = $existingRoute;
            $autoRoute->setType(AutoRouteInterface::TYPE_PRIMARY);

            return $autoRoute;
        }

        $uri = $this->uriGenerator->resolveConflict($uriContext, $uriContextCollection);
        $uriContext->setUri($uri);
    }
}
