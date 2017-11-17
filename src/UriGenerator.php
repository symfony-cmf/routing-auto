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

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class which handles URL generation and conflict resolution.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class UriGenerator implements UriGeneratorInterface
{
    /**
     * @var ServiceRegistry The auto route service registry
     */
    private $serviceRegistry;

    public function __construct(
        ServiceRegistry $serviceRegistry
    ) {
        $this->serviceRegistry = $serviceRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function generateUri(UriContext $uriContext)
    {
        $uriSchema = $uriContext->getUriSchema();
        $tokenProviderConfigs = $uriContext->getTokenProviderConfigs();

        $tokens = [];
        preg_match_all('/{(.*?)}/', $uriSchema, $matches);
        $tokenNames = $matches[1];

        foreach ($tokenNames as $index => $name) {
            if (!isset($tokenProviderConfigs[$name])) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown token "%s" in URI schema "%s"',
                    $name, $uriSchema
                ));
            }
            $tokenProviderConfig = $tokenProviderConfigs[$name];

            $tokenProvider = $this->serviceRegistry->getTokenProvider($tokenProviderConfig['name']);

            $optionsResolver = new OptionsResolver();
            $this->configureGlobalOptions($optionsResolver);
            $tokenProvider->configureOptions($optionsResolver);
            $tokenProviderOptions = $optionsResolver->resolve($tokenProviderConfig['options']);

            $tokenValue = $tokenProvider->provideValue($uriContext, $tokenProviderOptions);

            $isEmpty = empty($tokenValue) || '/' === $tokenValue;

            if ($isEmpty && false === $tokenProviderOptions['allow_empty']) {
                throw new \InvalidArgumentException(sprintf(
                    'Token provider "%s" returned an empty value for token "%s" with URI schema "%s"',
                    $tokenProviderConfig['name'], $name, $uriSchema
                ));
            }

            $tokenString = '{'.$name.'}';

            if ($isEmpty && true === $tokenProviderOptions['allow_empty']) {
                $isLast = count($tokenNames) === $index + 1;
                $tokens[$tokenString.'/'] = (string) $tokenValue;

                if ($isLast) {
                    $tokens['/'.$tokenString] = (string) $tokenValue;
                }
            }

            $tokens[$tokenString] = $tokenValue;
        }

        $uri = strtr($uriSchema, $tokens);

        if ('/' !== $uri[0]) {
            throw new \InvalidArgumentException(sprintf(
                'Generated non-absolute URI "%s" for object "%s"',
                $uri, get_class($uriContext->getSubject())
            ));
        }

        return $uri;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveConflict(UriContext $uriContext)
    {
        $conflictResolverConfig = $uriContext->getConflictResolverConfig();
        $conflictResolver = $this->serviceRegistry->getConflictResolver(
            $conflictResolverConfig['name']
        );

        return $conflictResolver->resolveConflict($uriContext);
    }

    /**
     * Configure options which apply to each token provider.
     *
     * @param OptionsResolver
     */
    private function configureGlobalOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefaults([
            'allow_empty' => false,
        ]);
    }
}
