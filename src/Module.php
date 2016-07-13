<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Versioning;

/**
 * Zend Framework module
 */
class Module
{
    /**
     * @var PrototypeRouteListener
     */
    private $prototypeRouteListener;

    /**
     * Retrieve module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Listen to ModuleManager init event.
     *
     * Attaches a PrototypeRouteListener to the module manager event manager.
     *
     * @param \Zend\ModuleManager\ModuleManager $moduleManager
     * @return void
     */
    public function init($moduleManager)
    {
        $this->getPrototypeRouteListener()->attach($moduleManager->getEventManager());
    }

    /**
     * Listen to zend-mvc bootstrap event.
     *
     * Attaches each of the Accept, ContentType, and Version listeners to the
     * application event manager.
     *
     * @param \Zend\Mvc\MvcEvent $e
     * @return void
     */
    public function onBootstrap($e)
    {
        $app      = $e->getTarget();
        $events   = $app->getEventManager();
        $services = $app->getServiceManager();
        $services->get('ZF\Versioning\AcceptListener')->attach($events);
        $services->get('ZF\Versioning\ContentTypeListener')->attach($events);
        $services->get('ZF\Versioning\VersionListener')->attach($events);
    }

    /**
     * Return the prototype route listener instance.
     *
     * Lazy-instantiates an instance if none previously registered.
     *
     * @return PrototypeRouteListener
     */
    public function getPrototypeRouteListener()
    {
        if ($this->prototypeRouteListener) {
            return $this->prototypeRouteListener;
        }

        $this->prototypeRouteListener = new PrototypeRouteListener();
        return $this->prototypeRouteListener;
    }
}
