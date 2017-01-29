<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Mapping\Loader;

use Symfony\Cmf\Component\RoutingAuto\Mapping\AutoRouteDefinition;
use Symfony\Cmf\Component\RoutingAuto\Mapping\ClassMetadata;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Parser as YamlParser;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class YmlFileLoader extends FileLoader
{
    /**
     * @var null|YamlParser
     */
    private $parser;

    /**
     * Loads a Yaml File.
     *
     * @param string      $path A Yaml file path
     * @param string|null $type
     *
     * @return ClassMetadata[]
     *
     * @throws \InvalidArgumentException When the $file cannot be parsed
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);

        if (!stream_is_local($path)) {
            throw new \InvalidArgumentException(sprintf('This is not a local file "%s".', $path));
        }

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('File "%s" not found.', $path));
        }

        $config = $this->getParser()->parse(file_get_contents($path));

        // empty file
        if (empty($config)) {
            return [];
        }

        if (!is_array($config)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $path));
        }

        $metadatas = [];
        foreach ($config as $className => $mappingNode) {
            $metadatas[] = $this->parseMappingNode($className, (array) $mappingNode, $path);
        }

        return $metadatas;
    }

    /**
     * @param string $className
     * @param array  $mappingNode
     * @param string $path
     */
    protected function parseMappingNode($className, $mappingNode, $path)
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException(sprintf('Configuration found for unknown class "%s" in "%s".', $className, $path));
        }

        $classMetadata = new ClassMetadata($className);

        $this->validateNode(
            $mappingNode,
            [
                'uri_schema',
                'definitions',
                'conflict_resolver',
                'defunct_route_handler',
                'extend',
                'token_providers',
            ],
            sprintf(
                'routing auto class metadata (%s)',
                $className
            )
        );

        if (!isset($mappingNode['definitions'])) {
            throw new \InvalidArgumentException(sprintf(
                'Mapping node for "%s" must define a list of auto route definitions under the `definitions` key.',
                $className
            ));
        }

        foreach ($mappingNode as $key => $value) {
            switch ($key) {
                case 'definitions':
                    foreach ($this->getAutoRouteDefinitions($value) as $definitionName => $definition) {
                        $classMetadata->setAutoRouteDefinition($definitionName, $definition);
                    }
                    break;
                case 'conflict_resolver':
                    $classMetadata->setConflictResolver($this->parseServiceConfig($mappingNode['conflict_resolver'], $className, $path));
                    break;
                case 'defunct_route_handler':
                    $classMetadata->setDefunctRouteHandler($this->parseServiceConfig($mappingNode['defunct_route_handler'], $className, $path));
                    break;
                case 'extend':
                    $classMetadata->setExtendedClass($mappingNode['extend']);
                    break;
                case 'token_providers':
                    foreach ($mappingNode['token_providers'] as $tokenName => $provider) {
                        $classMetadata->addTokenProvider($tokenName, $this->parseServiceConfig($provider, $className, $path));
                    }
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf(
                        'Unknown mapping key "%s"', $key
                    ));
            }
        }

        return $classMetadata;
    }

    /**
     * @param mixed  $service
     * @param string $className
     * @param string $path
     *
     * @return array
     */
    protected function parseServiceConfig($service, $className, $path)
    {
        $name = '';
        $options = [];

        if (is_string($service)) {
            // provider: method
            $name = $service;
        } elseif (1 === count($service) && isset($service[0])) {
            // provider: [method]
            $name = $service[0];
        } elseif (isset($service['name'])) {
            if (isset($service['options'])) {
                // provider: { name: method, options: { slugify: true } }
                $options = $service['options'];
            }

            // provider: { name: method }
            $name = $service['name'];
        } elseif (2 === count($service) && isset($service[0]) && isset($service[1])) {
            // provider: [method, { slugify: true }]
            $name = $service[0];
            $options = $service[1];
        } else {
            throw new \InvalidArgumentException(sprintf('Unknown builder service configuration for "%s" for class "%s" in "%s": %s', $name, $className, $path, json_encode($service)));
        }

        return ['name' => $name, 'options' => $options];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'yaml' === $type);
    }

    protected function getParser()
    {
        if (null === $this->parser) {
            $this->parser = new YamlParser();
        }

        return $this->parser;
    }

    protected function getAutoRouteDefinitions($definitionsNode)
    {
        if (!is_array($definitionsNode)) {
            throw new \InvalidArgumentException(sprintf(
                'Expected array or scalar definitionNode, got "%s"',
                is_object($definitionsNode) ? get_class($definitionsNode) : gettype($definitionsNode)
            ));
        }

        $definitions = [];
        foreach ($definitionsNode as $definitionName => $definitionNode) {
            $this->validateNode(
                $definitionNode,
                [
                    'uri_schema',
                    'defaults',
                ],
                'auto route definition'
            );

            // set default values
            $definitionNode = array_merge(
                [
                    'defaults' => [],
                ],
                $definitionNode
            );

            if (!isset($definitionNode['uri_schema'])) {
                throw new \InvalidArgumentException(
                    'All auto route definitions must have a `uri_schema` defined.'
                );
            }

            $definitions[$definitionName] = new AutoRouteDefinition($definitionNode['uri_schema'], $definitionNode['defaults']);
        }

        return $definitions;
    }

    /**
     * Ensure that $data contains only the keys given by $validKeys.
     *
     * @param mixed[]  $data
     * @param string[] $validKeys
     * @param string   $context
     *
     * @throws InvalidArgumentException
     *
     * @return mixed[]
     */
    protected function validateNode(array $data, array $validKeys, $context)
    {
        $diff = array_diff(array_keys($data), $validKeys);

        if (!$diff) {
            return;
        }

        throw new \InvalidArgumentException(sprintf(
            '[%s] Invalid keys "%s" Valid keys "%s"',
            $context,
            implode('", "', $diff),
            implode('", "', $validKeys)
        ));
    }
}
