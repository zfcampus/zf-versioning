<?php
/**
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
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
            __NAMESPACE__ => __DIR__,
        )));
    }

    /**
     * Retrieve module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array('factories' => array(
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
        $events->attach($services->get('ZF\Versioning\ContentTypeListener'));
    }
}
