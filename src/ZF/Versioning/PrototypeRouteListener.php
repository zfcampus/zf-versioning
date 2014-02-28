<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Versioning;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\ModuleManager\Listener\ConfigListener;
use Zend\ModuleManager\ModuleEvent;
use Zend\Stdlib\ArrayUtils;

class PrototypeRouteListener extends AbstractListenerAggregate
{
    /**
     * Match to prepend to versioned routes
     *
     * @var string
     */
    protected $versionRoutePrefix = '[/v:version]';

    /**
     * Constraints to introduce in versioned routes
     *
     * @var array
     */
    protected $versionRouteOptions = array(
        'defaults'    => array(
            'version' => 1,
        ),
        'constraints' => array(
            'version' => '\d+',
        ),
    );

    /**
     * Attach listener to ModuleEvent::EVENT_MERGE_CONFIG
     *
     * @param  EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(ModuleEvent::EVENT_MERGE_CONFIG, array($this, 'onMergeConfig'));
    }

    /**
     * Listen to ModuleEvent::EVENT_MERGE_CONFIG
     *
     * Looks for zf-versioning.url and router configuration; if both present,
     * injects the route prototype and adds a chain route to each route listed
     * in the zf-versioning.url array.
     *
     * @param  ModuleEvent $e
     */
    public function onMergeConfig(ModuleEvent $e)
    {
        $configListener = $e->getConfigListener();
        if (!$configListener instanceof ConfigListener) {
            return;
        }

        $config = $configListener->getMergedConfig(false);

        // Check for config keys
        if (!isset($config['zf-versioning'])
            || !isset($config['router'])
        ) {
            return;
        }

        // Do we need to inject a prototype?
        if (!isset($config['zf-versioning']['uri'])
            || !is_array($config['zf-versioning']['uri'])
            || empty($config['zf-versioning']['uri'])
        ) {
            return;
        }

        // Override default version of 1 with user-specified config value, if available.
        if (isset($config['zf-versioning']['default_version'])) {
            $this->versionRouteOptions['defaults']['version'] = $config['zf-versioning']['default_version'];
        }

        // Pre-process route list to strip out duplicates (often a result of
        // specifying nested routes)
        $routes   = $config['zf-versioning']['uri'];
        $filtered = array();
        foreach ($routes as $index => $route) {
            if (strstr($route, '/')) {
                $temp  = explode('/', $route, 2);
                $route = array_shift($temp);
            }
            if (in_array($route, $filtered)) {
                continue;
            }
            $filtered[] = $route;
        }
        $routes = $filtered;

        // Inject chained routes
        foreach ($routes as $routeName) {
            if (!isset($config['router']['routes'][$routeName])) {
                continue;
            }

            $config['router']['routes'][$routeName]['options']['route'] = $this->versionRoutePrefix
                . $config['router']['routes'][$routeName]['options']['route'];

            $config['router']['routes'][$routeName]['options'] = ArrayUtils::merge(
                $config['router']['routes'][$routeName]['options'],
                $this->versionRouteOptions
            );
        }

        // Reset merged config
        $configListener->setMergedConfig($config);
    }
}
