<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Versioning;

/**
 * ZF2 module
 */
class Module
{
    /**
     * Retrieve module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array('factories' => array(
            'ZF\Versioning\AcceptListener' => function ($services) {
                $config = [];
                if ($services->has('config')) {
                    $allConfig = $services->get('config');
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
                $config = [];
                if ($services->has('config')) {
                    $allConfig = $services->get('config');
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
        $prototypeRouteListener->attach($events);
    }

    public function onBootstrap($e)
    {
        $app      = $e->getTarget();
        $events   = $app->getEventManager();
        $services = $app->getServiceManager();
        $services->get('ZF\Versioning\AcceptListener')->attach($events);
        $services->get('ZF\Versioning\ContentTypeListener')->attach($events);
        $services->get('ZF\Versioning\VersionListener')->attach($events);
    }
}
