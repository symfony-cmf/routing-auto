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

use Symfony\Cmf\Component\RoutingAuto\Mapping\MetadataFactory;

/**
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class UriContextBuilder
{
    private $metadataFactory;
    private $uriGenerator;

    public function __construct(
        MetadataFactoryInterface $metadataFactory,
        UriGeneratorInterface $uriGenerator
    )
    {
        $this->metadataFactory = $metadataFactory;
        $this->uriGenerator = $uriGenerator;
    }

    public function buildCollection(UriContextCollection $collection, $locale)
    {
        $subject = $collection->getSubjectObject();
        $realClassName = $this->driver->getRealClassName(get_class($subject));
        $metadata = $this->metadataFactory->getMetadataForClass($realClassName);

        foreach ($metadata->getRouteMetadatas() as $routeMetadata) {
            // create and add uri context to stack
            $uriContext = $uriContextCollection->createUriContext($locale);
            $uriContext->setRouteMetadata($routeMetadata);
            $uriContextCollection->addUriContext($uriContext);

            // generate the URL
            $uri = $this->uriGenerator->generateUri($uriContext);

            // update the context with the URL
            $uriContext->setUri($uri);
        }
    }
}
