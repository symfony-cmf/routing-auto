<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Component\RoutingAuto\Mapping\Loader;

use Symfony\Cmf\Component\RoutingAuto\Mapping\ClassMetadata;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Config\Loader\FileLoader;

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
            return array();
        }

        if (!is_array($config)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $path));
        }

        $metadatas = array();
        foreach ($config as $className => $mappingNode) {
            $metadatas[] = $this->parseMappingNode($className, $mappingNode, $path);
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

        $validKeys = array(
            'uri_schema',
            'conflict_resolver',
            'defunct_route_handler',
            'extend',
            'token_providers',
        );

        foreach ($mappingNode as $key => $value) {
            if (!in_array($key, $validKeys)) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid configuration key "%s". Valid keys are "%s"',
                    $key, implode(',', $validKeys)
                ));
            }

            switch ($key) {
                case 'uri_schema':
                    $classMetadata->setUriSchema($value);
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
        $options = array();

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

        return array('name' => $name, 'options' => $options);
    }

    /**
     * {@inheritDoc}
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
}
