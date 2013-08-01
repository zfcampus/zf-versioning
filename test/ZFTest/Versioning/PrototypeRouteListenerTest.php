<?php
/**
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 */

namespace ZFTest\Versioning;

use PHPUnit_Framework_TestCase as TestCase;
use ReflectionObject;
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
            'status' => array(array('status')),
            'user'   => array(array('user')),
            'both'   => array(array('status', 'user')),
        );
    }

    /**
     * @dataProvider routesForWhichToVerifyPrototype
     */
    public function testPrototypeAddedToRoutesProvidedToListener(array $routes)
    {
        $this->config['zf-versioning'] = array('uri' => $routes);
        $this->configListener->setMergedConfig($this->config);
        $listener = new PrototypeRouteListener();
        $listener->onMergeConfig($this->event);

        $config = $this->configListener->getMergedConfig(false);
        $this->assertArrayHasKey('router', $config, var_export($config, 1));
        $routerConfig = $config['router'];
        $this->assertArrayHasKey('prototypes', $routerConfig, var_export($routerConfig, 1));
        $this->assertArrayHasKey('zf_ver_version', $routerConfig['prototypes']);

        $routesConfig = $routerConfig['routes'];
        foreach ($routes as $routeName) {
            $this->assertArrayHasKey($routeName, $routesConfig);
            $routeConfig = $routesConfig[$routeName];
            $this->assertArrayHasKey('chain_routes', $routeConfig);
            $this->assertEquals(array('zf_ver_version'), $routeConfig['chain_routes']);
        }
    }
}
