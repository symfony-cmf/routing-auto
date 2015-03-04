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
use Metadata\MetadataFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class which handles URL generation and conflict resolution
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class UriGenerator implements UriGeneratorInterface
{
    protected $driver;
    protected $metadataFactory;
    protected $serviceRegistry;

    /**
     * @param MetadataFactory   the metadata factory
     * @param AdapterInterface  the autoroute backend driver (odm ,orm, etc)
     * @param ServiceRegistry  the auto route service registry
     */
    public function __construct(
        MetadataFactoryInterface $metadataFactory,
        AdapterInterface $driver,
        ServiceRegistry $serviceRegistry
    )
    {
        $this->metadataFactory = $metadataFactory;
        $this->driver = $driver;
        $this->serviceRegistry = $serviceRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function generateUri(UriContext $uriContext)
    {
        $realClassName = $this->driver->getRealClassName(get_class($uriContext->getSubjectObject()));
        $metadata = $this->metadataFactory->getMetadataForClass($realClassName);

        $tokenProviderConfigs = $metadata->getTokenProviders();

        $tokens = array();
        preg_match_all('/{(.*?)}/', $metadata->getUriSchema(), $matches);
        $tokens = $matches[1];

        foreach ($tokens as $name) {
            if (!isset($tokenProviderConfigs[$name])) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown token "%s" in URI schema "%s"',
                    $name, $metadata->getUriSchema()
                ));
            }
            $options = $tokenProviderConfigs[$name];

            $tokenProvider = $this->serviceRegistry->getTokenProvider($options['name']);

            // I can see the utility of making this a singleton, but it is a massive
            // code smell to have this in a base class and be also part of the interface
            $optionsResolver = new OptionsResolver();
            $tokenProvider->configureOptions($optionsResolver);

            $tokens['{' . $name . '}'] = $tokenProvider->provideValue($uriContext, $optionsResolver->resolve($options['options']));
        }

        $uriSchema = $metadata->getUriSchema();
        $uri = strtr($uriSchema, $tokens);

        if (substr($uri, 0, 1) !== '/') {
            throw new \InvalidArgumentException(sprintf(
                'Generated non-absolute URI "%s" for object "%s"',
                $uri, $metadata->name
            ));
        }

        return $uri;
    }

    /**
     * {@inheritDoc}
     */
    public function resolveConflict(UriContext $uriContext)
    {
        $realClassName = $this->driver->getRealClassName(get_class($uriContext->getSubjectObject()));
        $metadata = $this->metadataFactory->getMetadataForClass($realClassName);

        $conflictResolverConfig = $metadata->getConflictResolver();
        $conflictResolver = $this->serviceRegistry->getConflictResolver(
            $conflictResolverConfig['name'], 
            $conflictResolverConfig['options']
        );
        $uri = $conflictResolver->resolveConflict($uriContext);

        return $uri;
    }
}
