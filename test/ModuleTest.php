<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Versioning;

use PHPUnit\Framework\TestCase;
use Zend\EventManager\EventManager;
use Zend\EventManager\Test\EventListenerIntrospectionTrait;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;
use ZF\Versioning\AcceptListener;
use ZF\Versioning\ContentTypeListener;
use ZF\Versioning\Module;
use ZF\Versioning\PrototypeRouteListener;
use ZF\Versioning\VersionListener;

class ModuleTest extends TestCase
{
    use EventListenerIntrospectionTrait;

    public function setUp()
    {
        $this->app = new TestAsset\Application();
        $this->services = new ServiceManager();
        $this->app->setServiceManager($this->services);
        $this->events = new EventManager();
        $this->app->setEventManager($this->events);

        $this->module = new Module();
    }

    public function testOnBootstrapMethodRegistersListenersWithEventManager()
    {
        $config = include __DIR__ . '/../config/module.config.php';
        (new Config($config['service_manager']))->configureServiceManager($this->services);

        $event = new MvcEvent();
        $event->setTarget($this->app);

        $this->module->onBootstrap($event);

        $listeners = [
            ContentTypeListener::class => -40,
            AcceptListener::class => -40,
            VersionListener::class => -41,
        ];

        foreach ($listeners as $class => $priority) {
            $listener = $this->services->get($class);
            $this->assertListenerAtPriority(
                [$listener, 'onRoute'],
                $priority,
                MvcEvent::EVENT_ROUTE,
                $this->events,
                sprintf('Listener %s at priority %s was not registered', $class, $priority)
            );
        }
    }

    public function testInitMethodRegistersPrototypeListenerWithModuleEventManager()
    {
        $moduleManager = new ModuleManager([]);
        $this->module->init($moduleManager);

        $listener = $this->module->getPrototypeRouteListener();
        $this->assertInstanceOf(PrototypeRouteListener::class, $listener);

        $events = $moduleManager->getEventManager();
        $this->assertListenerAtPriority(
            [$listener, 'onMergeConfig'],
            1,
            ModuleEvent::EVENT_MERGE_CONFIG,
            $events
        );
    }
}
