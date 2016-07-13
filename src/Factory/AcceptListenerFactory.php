<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Versioning\Factory;

use Interop\Container\ContainerInterface;
use ZF\Versioning\AcceptListener;

class AcceptListenerFactory
{
    /**
     * @param ContainerInterface $container
     * @return AcceptListener
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = isset($config['zf-versioning']['content-type'])
            ? $config['zf-versioning']['content-type']
            : [];

        $listener = new AcceptListener();
        foreach ($config as $regexp) {
            $listener->addRegexp($regexp);
        }
        return $listener;
    }
}
