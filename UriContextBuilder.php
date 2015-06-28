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
 * Builds up the URI Context Collections.
 */
class UriContextBuilder
{
    private $metadataFactory;
    private $uriGenerator;

    /**
     * @param MetadataFactoryInterface $metadataFactory
     * @param UriGeneratorInterface $uriGenerator
     */
    public function __construct(
        MetadataFactoryInterface $metadataFactory,
        UriGeneratorInterface $uriGenerator
    )
    {
        $this->metadataFactory = $metadataFactory;
        $this->uriGenerator = $uriGenerator;
    }

    /**
     * Populates the given UriContextCollection (with associated subject)
     * with processed UriContexts.
     *
     * @param UriContextCollection $collection
     * @param string $locale
     */
    public function build(UriContextCollection $collection, $locale)
    {
        $subject = $collection->getSubjectObject();
        $realClassName = $this->driver->getRealClassName(get_class($subject));
        $metadata = $this->metadataFactory->getMetadataForClass($realClassName);

        foreach ($metadata->getRouteMetadatas() as $routeMetadata) {
            // create and add uri context to stack
            $uriContext = $uriContextCollection->createUriContext($locale, $routeMetadata);
            $uriContextCollection->addUriContext($uriContext);

            // generate the URL
            $uri = $this->uriGenerator->generateUri($uriContext);

            // update the context with the URL
            $uriContext->setUri($uri);
        }
    }
}
