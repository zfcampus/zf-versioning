<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Versioning;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\EventManager\EventManager;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use ZF\Versioning\ContentTypeListener;
use ZF\Versioning\Module;

class ModuleTest extends TestCase
{
    public function setUp()
    {
        $this->app = new TestAsset\Application();
        $this->services = new ServiceManager();
        $this->app->setServiceManager($this->services);
        $this->events = new EventManager();
        $this->app->setEventManager($this->events);

        $this->module = new Module();
    }

    public function testModuleDefinesServiceForContentTypeListener()
    {
        $config = $this->module->getServiceConfig();
        $this->assertArrayHasKey('factories', $config);
        $this->assertArrayHasKey('ZF\Versioning\ContentTypeListener', $config['factories']);
        $this->assertInstanceOf('Closure', $config['factories']['ZF\Versioning\ContentTypeListener']);
        return $config['factories']['ZF\Versioning\ContentTypeListener'];
    }

    /**
     * @depends testModuleDefinesServiceForContentTypeListener
     */
    public function testModuleDefinesServiceForAcceptListener($factory)
    {
        $config = $this->module->getServiceConfig();
        $this->assertArrayHasKey('factories', $config);
        $this->assertArrayHasKey('ZF\Versioning\AcceptListener', $config['factories']);
        $this->assertInstanceOf('Closure', $config['factories']['ZF\Versioning\AcceptListener']);
    }

    /**
     * @depends testModuleDefinesServiceForContentTypeListener
     */
    public function testServiceFactoryDefinedInModuleReturnsListener($factory)
    {
        $listener = $factory($this->services);
        $this->assertInstanceOf('ZF\Versioning\ContentTypeListener', $listener);
    }

    /**
     * @depends testModuleDefinesServiceForContentTypeListener
     */
    public function testServiceFactoryDefinedInModuleUsesConfigServiceWhenDefiningListener($factory)
    {
        $config = array(
            'zf-versioning' => array(
                'content-type' => array(
                    '#^application/vendor\.(?P<vendor>mwop)\.(?P<resource>user|status)$#',
                ),
            ),
        );
        $this->services->setService('config', $config);

        $listener = $factory($this->services);
        $this->assertInstanceOf('ZF\Versioning\ContentTypeListener', $listener);
        $this->assertAttributeContains($config['zf-versioning']['content-type'][0], 'regexes', $listener);
    }

    /**
     * @depends testModuleDefinesServiceForContentTypeListener
     */
    public function testOnBootstrapMethodRegistersListenersWithEventManager($factory)
    {
        $serviceConfig = $this->module->getServiceConfig();
        $this->services->setFactory('ZF\Versioning\ContentTypeListener', $serviceConfig['factories']['ZF\Versioning\ContentTypeListener']);
        $this->services->setFactory('ZF\Versioning\AcceptListener', $serviceConfig['factories']['ZF\Versioning\AcceptListener']);
        $this->services->setInvokableClass('ZF\Versioning\VersionListener', 'ZF\Versioning\VersionListener');

        $event = new MvcEvent();
        $event->setTarget($this->app);

        $this->module->onBootstrap($event);

        $listeners = $this->events->getListeners(MvcEvent::EVENT_ROUTE);
        $this->assertEquals(3, count($listeners));
        $this->assertTrue($listeners->hasPriority(-40));

        $test = array();
        foreach ($listeners as $listener) {
            $callback = $listener->getCallback();
            $test[]   = array_shift($callback);
        }

        $expected = array(
            'ZF\Versioning\ContentTypeListener',
            'ZF\Versioning\AcceptListener',
            'ZF\Versioning\VersionListener',
        );
        foreach ($expected as $class) {
            $listener = $this->services->get($class);
            $this->assertContains($listener, $test);
        }
    }

    public function testInitMethodRegistersPrototypeListenerWithModuleEventManager()
    {
        $moduleManager = new ModuleManager(array());
        $this->module->init($moduleManager);

        $events    = $moduleManager->getEventManager();
        $listeners = $events->getListeners(ModuleEvent::EVENT_MERGE_CONFIG);
        $this->assertEquals(1, count($listeners));
        $this->assertTrue($listeners->hasPriority(1));
        $callback = $listeners->getIterator()->current()->getCallback();
        $test     = array_shift($callback);
        $this->assertInstanceOf('ZF\Versioning\PrototypeRouteListener', $test);
    }
}
