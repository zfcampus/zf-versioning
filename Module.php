<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Versioning;

/**
 * ZF2 module
 */
class Module
{
    /**
     * Retrieve autoloader configuration
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array('Zend\Loader\StandardAutoloader' => array('namespaces' => array(
            __NAMESPACE__ => __DIR__ . '/src/',
        )));
    }

    /**
     * Retrieve module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array('factories' => array(
            'ZF\Versioning\AcceptListener' => function ($services) {
                $config = array();
                if ($services->has('Config')) {
                    $allConfig = $services->get('Config');
                    if (isset($allConfig['zf-versioning'])
                        && isset($allConfig['zf-versioning']['content-type'])
                        && is_array($allConfig['zf-versioning']['content-type'])
                    ) {
                        $config = $allConfig['zf-versioning']['content-type'];
                    }
                }

                $listener = new AcceptListener();
                foreach ($config as $regexp) {
                    $listener->addRegexp($regexp);
                }
                return $listener;
            },
            'ZF\Versioning\ContentTypeListener' => function ($services) {
                $config = array();
                if ($services->has('Config')) {
                    $allConfig = $services->get('Config');
                    if (isset($allConfig['zf-versioning'])
                        && isset($allConfig['zf-versioning']['content-type'])
                        && is_array($allConfig['zf-versioning']['content-type'])
                    ) {
                        $config = $allConfig['zf-versioning']['content-type'];
                    }
                }

                $listener = new ContentTypeListener();
                foreach ($config as $regexp) {
                    $listener->addRegexp($regexp);
                }
                return $listener;
            },
        ));
    }

    public function init($moduleManager)
    {
        $events = $moduleManager->getEventManager();
        $prototypeRouteListener = new PrototypeRouteListener();
        $events->attach($prototypeRouteListener);
    }

    public function onBootstrap($e)
    {
        $app      = $e->getTarget();
        $events   = $app->getEventManager();
        $services = $app->getServiceManager();
        $events->attach($services->get('ZF\Versioning\AcceptListener'));
        $events->attach($services->get('ZF\Versioning\ContentTypeListener'));
        $events->attach($services->get('ZF\Versioning\VersionListener'));
    }
}
