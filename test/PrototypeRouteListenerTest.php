<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Versioning;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\ModuleManager\Listener\ConfigListener;
use Zend\ModuleManager\ModuleEvent;
use ZF\Versioning\PrototypeRouteListener;

class PrototypeRouteListenerTest extends TestCase
{
    public function setUp()
    {
        $this->config = array('router' => array(
            'routes' => array(
                'status' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/status[/:id]',
                        'defaults' => array(
                            'controller' => 'StatusController',
                        ),
                    ),
                ),
                'user' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/user[/:id]',
                        'defaults' => array(
                            'controller' => 'UserController',
                        ),
                    ),
                ),
            ),
        ));
        $this->configListener = new ConfigListener();
        $this->configListener->setMergedConfig($this->config);
        $this->event = new ModuleEvent();
        $this->event->setConfigListener($this->configListener);

    }

    public function routesWithoutPrototype()
    {
        return array(
            'none'   => array(array()),
            'status' => array(array('status')),
            'user'   => array(array('user')),
            'both'   => array(array('status', 'user')),
        );
    }

    /**
     * @dataProvider routesWithoutPrototype
     */
    public function testEmptyConfigurationDoesNotInjectPrototypes(array $routes)
    {
        $listener = new PrototypeRouteListener();
        $listener->onMergeConfig($this->event);

        $config = $this->configListener->getMergedConfig(false);
        $this->assertArrayHasKey('router', $config, var_export($config, 1));
        $routerConfig = $config['router'];
        $this->assertArrayNotHasKey('prototypes', $routerConfig);

        $routesConfig = $routerConfig['routes'];
        foreach ($routes as $routeName) {
            $this->assertArrayHasKey($routeName, $routesConfig);
            $routeConfig = $routesConfig[$routeName];
            $this->assertArrayNotHasKey('chain_routes', $routeConfig);
        }
    }

    public function routesForWhichToVerifyPrototype()
    {
        return array(
            'status' => array(array('status'), 1),
            'user'   => array(array('user'), 2),
            'both'   => array(array('status', 'user')),
        );
    }

    /**
     * @dataProvider routesForWhichToVerifyPrototype
     */
    public function testPrototypeAddedToRoutesProvidedToListener(array $routes, $apiVersion = null)
    {
        $this->config['zf-versioning'] = array(
            'uri' => $routes
        );

        if (!empty($apiVersion)) {
            $this->config['zf-versioning']['default_version'] = $apiVersion;
        } else {
            $apiVersion = 1;
        }

        $this->configListener->setMergedConfig($this->config);
        $listener = new PrototypeRouteListener();
        $listener->onMergeConfig($this->event);

        $config = $this->configListener->getMergedConfig(false);
        $this->assertArrayHasKey('router', $config, var_export($config, 1));
        $routerConfig = $config['router'];

        $routesConfig = $routerConfig['routes'];
        foreach ($routes as $routeName) {
            $this->assertArrayHasKey($routeName, $routesConfig);
            $routeConfig = $routesConfig[$routeName];
            $this->assertArrayHasKey('options', $routeConfig);
            $options = $routeConfig['options'];

            $this->assertArrayHasKey('route', $options);
            $this->assertSame(0, strpos($options['route'], '[/v:version]'));

            $this->assertArrayHasKey('constraints', $options);
            $this->assertArrayHasKey('version', $options['constraints']);
            $this->assertEquals('\d+', $options['constraints']['version']);

            $this->assertArrayHasKey('defaults', $options);
            $this->assertArrayHasKey('version', $options['defaults']);
            $this->assertEquals($apiVersion, $options['defaults']['version']);
        }
    }
}
