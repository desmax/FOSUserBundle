<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\UserBundle\Tests\Routing;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Symfony\Component\Routing\RouteCollection;

class RoutingTest extends TestCase
{
    /**
     * @dataProvider loadRoutingProvider
     *
     * @param string $routeName
     * @param string $path
     * @param array  $methods
     */
    public function testLoadRouting($routeName, $path, array $methods)
    {
        $locator = new FileLocator();
        $loader = new XmlFileLoader($locator);

        $collection = new RouteCollection();
        $collection->addCollection($loader->load(__DIR__.'/../../Resources/config/routing/security.xml'));

        $route = $collection->get($routeName);
        $this->assertNotNull($route, sprintf('The route "%s" should exists', $routeName));
        $this->assertSame($path, $route->getPath());
        $this->assertSame($methods, $route->getMethods());
    }

    /**
     * @return array
     */
    public function loadRoutingProvider()
    {
        return array(
            array('fos_user_security_check', '/login_check', array('POST')),
            array('fos_user_security_logout', '/logout', array('GET', 'POST')),
        );
    }
}
