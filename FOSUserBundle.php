<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\UserBundle;

use FOS\UserBundle\DependencyInjection\Compiler\CheckForMailerPass;
use FOS\UserBundle\DependencyInjection\Compiler\CheckForSessionPass;
use FOS\UserBundle\DependencyInjection\Compiler\InjectRememberMeServicesPass;
use FOS\UserBundle\DependencyInjection\Compiler\InjectUserCheckerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Matthieu Bontemps <matthieu@knplabs.com>
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class FOSUserBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new InjectUserCheckerPass());
        $container->addCompilerPass(new InjectRememberMeServicesPass());
        $container->addCompilerPass(new CheckForSessionPass());
        $container->addCompilerPass(new CheckForMailerPass());

    }
}
