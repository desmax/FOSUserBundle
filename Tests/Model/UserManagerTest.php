<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\UserBundle\Tests\Model;

use FOS\UserBundle\Model\UserManager;
use PHPUnit\Framework\TestCase;

class UserManagerTest extends TestCase
{
    /** @var UserManager|\PHPUnit_Framework_MockObject_MockObject */
    private $manager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $passwordUpdater;

    protected function setUp()
    {
        $this->passwordUpdater = $this->getMockBuilder('FOS\UserBundle\Util\PasswordUpdaterInterface')->getMock();

        $this->manager = $this->getUserManager(array(
            $this->passwordUpdater,
        ));
    }

    public function testUpdatePassword()
    {
        $user = $this->getUser();

        $this->passwordUpdater->expects($this->once())
            ->method('hashPassword')
            ->with($this->identicalTo($user));

        $this->manager->updatePassword($user);
    }

    public function testFindUserByEmail()
    {
        $this->manager->expects($this->once())
            ->method('findUserBy')
            ->with($this->equalTo(array('email' => 'jack@email.org')));

        $this->manager->findUserByEmail('jack@email.org');
    }

    /**
     * @return mixed
     */
    private function getUser()
    {
        return $this->getMockBuilder('FOS\UserBundle\Model\User')
            ->getMockForAbstractClass();
    }

    /**
     * @param array $args
     *
     * @return mixed
     */
    private function getUserManager(array $args)
    {
        return $this->getMockBuilder('FOS\UserBundle\Model\UserManager')
            ->setConstructorArgs($args)
            ->getMockForAbstractClass();
    }
}
