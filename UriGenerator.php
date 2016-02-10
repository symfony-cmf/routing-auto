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

use Metadata\MetadataFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class which handles URL generation and conflict resolution.
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
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->driver = $driver;
        $this->serviceRegistry = $serviceRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function generateUri(UriContext $uriContext)
    {
        $realClassName = $this->driver->getRealClassName(get_class($uriContext->getSubjectObject()));
        $metadata = $this->metadataFactory->getMetadataForClass($realClassName);
        $uriSchema = $metadata->getUriSchema();

        $tokenProviderConfigs = $metadata->getTokenProviders();

        $tokens = array();
        preg_match_all('/{(.*?)}/', $metadata->getUriSchema(), $matches);
        $tokenNames = $matches[1];

        foreach ($tokenNames as $index => $name) {
            if (!isset($tokenProviderConfigs[$name])) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown token "%s" in URI schema "%s"',
                    $name, $metadata->getUriSchema()
                ));
            }
            $tokenProviderConfig = $tokenProviderConfigs[$name];

            $tokenProvider = $this->serviceRegistry->getTokenProvider($tokenProviderConfig['name']);

            $optionsResolver = new OptionsResolver();
            $this->configureGlobalOptions($optionsResolver);
            $tokenProvider->configureOptions($optionsResolver);
            $tokenProviderOptions = $optionsResolver->resolve($tokenProviderConfig['options']);

            $tokenValue = $tokenProvider->provideValue($uriContext, $tokenProviderOptions);

            $isEmpty = empty($tokenValue) || $tokenValue == '/';

            if ($isEmpty && false === $tokenProviderOptions['allow_empty']) {
                throw new \InvalidArgumentException(sprintf(
                    'Token provider "%s" returned an empty value for token "%s" with URI schema "%s"',
                    $tokenProviderConfig['name'], $name, $uriSchema
                ));
            }

            $tokenString = '{'.$name.'}';

            if ($isEmpty && true === $tokenProviderOptions['allow_empty']) {
                $isLast = count($tokenNames) == $index + 1;
                $tokens[$tokenString.'/'] = (string) $tokenValue;

                if ($isLast) {
                    $tokens['/'.$tokenString] = (string) $tokenValue;
                }
            }

            $tokens[$tokenString] = $tokenValue;
        }

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
     * {@inheritdoc}
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

    /**
     * Configure options which apply to each token provider.
     *
     * @param OptionsResolver
     */
    private function configureGlobalOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefaults(array(
            'allow_empty' => false,
        ));
    }
}
