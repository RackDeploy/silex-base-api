<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Overrides;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Routing\Loader\YamlFileLoader;

/**
 * YamlFileLoader loads Yaml routing files.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Tobias Schultze <http://tobion.de>
 */
class SwaggerYamlFileLoader extends YamlFileLoader
{
    private $yamlParser;
    /**
     * Loads a Yaml file.
     *
     * @param string      $file A Yaml file path
     * @param string|null $type The resource type
     *
     * @return RouteCollection A RouteCollection instance
     *
     * @throws \InvalidArgumentException When a route can't be parsed because YAML is invalid
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter,PHPMD.ElseExpression)
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

        if (null === $this->yamlParser) {
            $this->yamlParser = new YamlParser();
        }

        try {
            $parsedConfig = $this->yamlParser->parse(file_get_contents($path));
            $parsedConfig = $parsedConfig['paths'];
        } catch (ParseException $e) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not contain valid YAML.', $path), 0, $e);
        }

        $collection = new RouteCollection();
        $collection->addResource(new FileResource($path));

        // empty file
        if (null === $parsedConfig) {
            return $collection;
        }

        // not an array
        if (!is_array($parsedConfig)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $path));
        }

        foreach ($parsedConfig as $name => $config) {
            //$this->validate($config, $name, $path);

            if (isset($config['resource'])) {
                $this->parseImport($collection, $config, $path, $file);
            } else {
                $this->parseRoute($collection, $name, $config, $path);
            }
        }

        return $collection;
    }

    /**
     * Returns the option if set, otherwise returns the override value.
     *
     * @param array           $option               Array to check
     * @param mixed           $overrideOption       Default value to return
     *
     * @return mixed A validated set option
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getOption(array $collection, $option, $overrideOption)
    {
        return isset($collection[$option]) ? $collection[$option] : $overrideOption;
    }

    /**
     * Parses a route and adds it to the RouteCollection.
     *
     * @param RouteCollection $collection A RouteCollection instance
     * @param string          $name       Route name
     * @param array           $config     Route definition
     * @param string          $path       Full path of the YAML file being processed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function parseRoute(RouteCollection $collection, $resource, array $config, $path)
    {
        //$name = preg_replace("/[^A-Za-z0-9 ]/", '-', $resource);

        foreach ($config as $key => $value) {
            // check for initial variables
            $methods = $key;

            // check for x-silex overrides
            $params = $this->getOption($value, 'x-silex', array());
            $defaults = $this->getOption($params, 'defaults', array());
            $requirements = $this->getOption($params, 'requirements', array());
            $options = $this->getOption($params, 'options', array());
            $host = $this->getOption($params, 'host', '');
            $schemes = $this->getOption($params, 'schemes', array());
            $methods = $this->getOption($params, 'methods', $methods);
            $condition = $this->getOption($params, 'condition', null);

            $name = $resource . '-' . $methods;

            $route = new Route(
                $resource,
                $defaults,
                $requirements,
                $options,
                $host,
                $schemes,
                $methods,
                $condition
            );

            $collection->add($name, $route);
        }
    }
}
