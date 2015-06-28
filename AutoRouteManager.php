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
use Symfony\Cmf\Component\RoutingAuto\UriContextBuilder;

/**
 * This class is concerned with the automatic creation of route objects.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AutoRouteManager
{
    protected $adapter;
    protected $uriContextBuilder;
    protected $defunctRouteHandler;

    private $pendingUriContextCollections = array();

    /**
     * @param AdapterInterface             $adapter             Database adapter
     * @param UriGeneratorInterface        $uriContextBuilder        Routing auto URL generator
     * @param DefunctRouteHandlerInterface $defunctRouteHandler Handler for defunct routes
     */
    public function __construct(
        AdapterInterface $adapter,
        UriContextBuilder $uriContextBuilder,
        DefunctRouteHandlerInterface $defunctRouteHandler
    )
    {
        $this->adapter = $adapter;
        $this->uriContextBuilder = $uriContextBuilder;
        $this->defunctRouteHandler = $defunctRouteHandler;
    }

    /**
     * @param object $document
     */
    public function buildUriContextCollection(UriContextCollection $uriContextCollection)
    {
        $this->buildUriContextsForDocument($uriContextCollection);

        foreach ($uriContextCollection->getUriContexts() as $uriContext) {
            $existingRoute = $this->adapter->findRouteForUri($uriContext->getUri(), $uriContext);

            $autoRoute = null;

            if ($existingRoute) {
                $isSameContent = $this->adapter->compareAutoRouteContent($existingRoute, $uriContextCollection->getSubjectObject());

                if ($isSameContent) {
                    $autoRoute = $existingRoute;
                    $autoRoute->setType(AutoRouteInterface::TYPE_PRIMARY);
                } else {
                    $uri = $this->resolveConflict($uriContext->getUri());
                    $uriContext->setUri($uri);
                }
            }

            if (!$autoRoute) {
                $autoRouteTag = $this->adapter->generateAutoRouteTag($uriContext);
                $autoRoute = $this->adapter->createAutoRoute($uriContext, $uriContextCollection->getSubjectObject(), $autoRouteTag);
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
    private function buildUriContextsForDocument(UriContextCollection $uriContextCollection)
    {
        $locales = $this->adapter->getLocales($uriContextCollection->getSubjectObject()) ? : array(null);

        foreach ($locales as $locale) {
            if (null !== $locale) {
                $this->adapter->translateObject($uriContextCollection->getSubjectObject(), $locale);
            }

            $this->uriContextBuilder->build($uriContextCollection, $locale);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function resolveConflict(UriContext $uriContext)
    {
        $metadata = $uriContext->getRouteMetadata();
        $config = $metadata->getConflictResolver();
        $conflictResolver = $this->serviceRegistry->getConflictResolver(
            $config['name'], 
            $config['options']
        );
        $uri = $conflictResolver->resolveConflict($uriContext);

        return $uri;
    }

}
