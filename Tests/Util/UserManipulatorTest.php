<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\UserBundle\Tests\Util;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Tests\TestUser;
use FOS\UserBundle\Util\UserManipulator;
use PHPUnit\Framework\TestCase;

class UserManipulatorTest extends TestCase
{
    public function testCreate()
    {
        $userManagerMock = $this->getMockBuilder('FOS\UserBundle\Model\UserManagerInterface')->getMock();
        $user = new TestUser();

        $password = 'test_password';
        $email = 'test@email.org';
        $active = true; // it is enabled
        $superadmin = false;

        $userManagerMock->expects($this->once())
            ->method('createUser')
            ->will($this->returnValue($user));

        $userManagerMock->expects($this->once())
            ->method('updateUser')
            ->will($this->returnValue($user))
            ->with($this->isInstanceOf('FOS\UserBundle\Tests\TestUser'));

        $eventDispatcherMock = $this->getEventDispatcherMock(FOSUserEvents::USER_CREATED, true);

        $requestStackMock = $this->getRequestStackMock(true);

        $manipulator = new UserManipulator($userManagerMock, $eventDispatcherMock, $requestStackMock);
        $manipulator->create($password, $email, $active, $superadmin);

        $this->assertSame($password, $user->getPlainPassword());
        $this->assertSame($email, $user->getEmail());
        $this->assertSame($active, $user->isEnabled());
        $this->assertSame($superadmin, $user->isSuperAdmin());
    }

    public function testActivateWithValidEmail()
    {
        $userManagerMock = $this->getMockBuilder('FOS\UserBundle\Model\UserManagerInterface')->getMock();
        $email = 'test_email@email.org';

        $user = new TestUser();
        $user->setEmail($email);
        $user->setEnabled(false);

        $userManagerMock->expects($this->once())
            ->method('findUserByEmail')
            ->will($this->returnValue($user))
            ->with($this->equalTo($email));

        $userManagerMock->expects($this->once())
            ->method('updateUser')
            ->will($this->returnValue($user))
            ->with($this->isInstanceOf('FOS\UserBundle\Tests\TestUser'));

        $eventDispatcherMock = $this->getEventDispatcherMock(FOSUserEvents::USER_ACTIVATED, true);

        $requestStackMock = $this->getRequestStackMock(true);

        $manipulator = new UserManipulator($userManagerMock, $eventDispatcherMock, $requestStackMock);
        $manipulator->activate($email);

        $this->assertSame($email, $user->getEmail());
        $this->assertTrue($user->isEnabled());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testActivateWithInvalidEmail()
    {
        $userManagerMock = $this->getMockBuilder('FOS\UserBundle\Model\UserManagerInterface')->getMock();
        $invalidEmail = 'invalid_email';

        $userManagerMock->expects($this->once())
            ->method('findUserByEmail')
            ->will($this->returnValue(null))
            ->with($this->equalTo($invalidEmail));

        $userManagerMock->expects($this->never())
            ->method('updateUser');

        $eventDispatcherMock = $this->getEventDispatcherMock(FOSUserEvents::USER_ACTIVATED, false);

        $requestStackMock = $this->getRequestStackMock(false);

        $manipulator = new UserManipulator($userManagerMock, $eventDispatcherMock, $requestStackMock);
        $manipulator->activate($invalidEmail);
    }

    public function testDeactivateWithValidEmail()
    {
        $userManagerMock = $this->getMockBuilder('FOS\UserBundle\Model\UserManagerInterface')->getMock();
        $email = 'test_email@email.org';

        $user = new TestUser();
        $user->setEmail($email);
        $user->setEnabled(true);

        $userManagerMock->expects($this->once())
            ->method('findUserByEmail')
            ->will($this->returnValue($user))
            ->with($this->equalTo($email));

        $userManagerMock->expects($this->once())
            ->method('updateUser')
            ->will($this->returnValue($user))
            ->with($this->isInstanceOf('FOS\UserBundle\Tests\TestUser'));

        $eventDispatcherMock = $this->getEventDispatcherMock(FOSUserEvents::USER_DEACTIVATED, true);

        $requestStackMock = $this->getRequestStackMock(true);

        $manipulator = new UserManipulator($userManagerMock, $eventDispatcherMock, $requestStackMock);
        $manipulator->deactivate($email);

        $this->assertSame($email, $user->getEmail());
        $this->assertFalse($user->isEnabled());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDeactivateWithInvalidEmail()
    {
        $userManagerMock = $this->getMockBuilder('FOS\UserBundle\Model\UserManagerInterface')->getMock();
        $invalidEmail = 'invalid_email';

        $userManagerMock->expects($this->once())
            ->method('findUserByEmail')
            ->will($this->returnValue(null))
            ->with($this->equalTo($invalidEmail));

        $userManagerMock->expects($this->never())
            ->method('updateUser');

        $eventDispatcherMock = $this->getEventDispatcherMock(FOSUserEvents::USER_DEACTIVATED, false);

        $requestStackMock = $this->getRequestStackMock(false);

        $manipulator = new UserManipulator($userManagerMock, $eventDispatcherMock, $requestStackMock);
        $manipulator->deactivate($invalidEmail);
    }

    public function testPromoteWithValidEmail()
    {
        $userManagerMock = $this->getMockBuilder('FOS\UserBundle\Model\UserManagerInterface')->getMock();
        $email = 'test_email@email.org';

        $user = new TestUser();
        $user->setEmail($email);
        $user->setSuperAdmin(false);

        $userManagerMock->expects($this->once())
            ->method('findUserByEmail')
            ->will($this->returnValue($user))
            ->with($this->equalTo($email));

        $userManagerMock->expects($this->once())
            ->method('updateUser')
            ->will($this->returnValue($user))
            ->with($this->isInstanceOf('FOS\UserBundle\Tests\TestUser'));

        $eventDispatcherMock = $this->getEventDispatcherMock(FOSUserEvents::USER_PROMOTED, true);

        $requestStackMock = $this->getRequestStackMock(true);

        $manipulator = new UserManipulator($userManagerMock, $eventDispatcherMock, $requestStackMock);
        $manipulator->promote($email);

        $this->assertSame($email, $user->getEmail());
        $this->assertTrue($user->isSuperAdmin());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPromoteWithInvalidEmail()
    {
        $userManagerMock = $this->getMockBuilder('FOS\UserBundle\Model\UserManagerInterface')->getMock();
        $invalidEmail = 'invalid_email';

        $userManagerMock->expects($this->once())
            ->method('findUserByEmail')
            ->will($this->returnValue(null))
            ->with($this->equalTo($invalidEmail));

        $userManagerMock->expects($this->never())
            ->method('updateUser');

        $eventDispatcherMock = $this->getEventDispatcherMock(FOSUserEvents::USER_PROMOTED, false);

        $requestStackMock = $this->getRequestStackMock(false);

        $manipulator = new UserManipulator($userManagerMock, $eventDispatcherMock, $requestStackMock);
        $manipulator->promote($invalidEmail);
    }

    public function testDemoteWithValidEmail()
    {
        $userManagerMock = $this->getMockBuilder('FOS\UserBundle\Model\UserManagerInterface')->getMock();
        $email = 'test_email@email.org';

        $user = new TestUser();
        $user->setEmail($email);
        $user->setSuperAdmin(true);

        $userManagerMock->expects($this->once())
            ->method('findUserByEmail')
            ->will($this->returnValue($user))
            ->with($this->equalTo($email));

        $userManagerMock->expects($this->once())
            ->method('updateUser')
            ->will($this->returnValue($user))
            ->with($this->isInstanceOf('FOS\UserBundle\Tests\TestUser'));

        $eventDispatcherMock = $this->getEventDispatcherMock(FOSUserEvents::USER_DEMOTED, true);

        $requestStackMock = $this->getRequestStackMock(true);

        $manipulator = new UserManipulator($userManagerMock, $eventDispatcherMock, $requestStackMock);
        $manipulator->demote($email);

        $this->assertSame($email, $user->getEmail());
        $this->assertFalse($user->isSuperAdmin());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDemoteWithInvalidEmail()
    {
        $userManagerMock = $this->getMockBuilder('FOS\UserBundle\Model\UserManagerInterface')->getMock();
        $invalidEmail = 'invalid_email';

        $userManagerMock->expects($this->once())
            ->method('findUserByEmail')
            ->will($this->returnValue(null))
            ->with($this->equalTo($invalidEmail));

        $userManagerMock->expects($this->never())
            ->method('updateUser');

        $eventDispatcherMock = $this->getEventDispatcherMock(FOSUserEvents::USER_DEMOTED, false);

        $requestStackMock = $this->getRequestStackMock(false);

        $manipulator = new UserManipulator($userManagerMock, $eventDispatcherMock, $requestStackMock);
        $manipulator->demote($invalidEmail);
    }

    public function testAddRole()
    {
        $userManagerMock = $this->getMockBuilder('FOS\UserBundle\Model\UserManagerInterface')->getMock();
        $email = 'test_email@email.org';
        $userRole = 'test_role';
        $user = new TestUser();

        $userManagerMock->expects($this->exactly(2))
            ->method('findUserByEmail')
            ->will($this->returnValue($user))
            ->with($this->equalTo($email));

        $eventDispatcherMock = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
        $requestStackMock = $this->getRequestStackMock(false);

        $manipulator = new UserManipulator($userManagerMock, $eventDispatcherMock, $requestStackMock);

        $this->assertTrue($manipulator->addRole($email, $userRole));
        $this->assertFalse($manipulator->addRole($email, $userRole));
        $this->assertTrue($user->hasRole($userRole));
    }

    public function testRemoveRole()
    {
        $userManagerMock = $this->getMockBuilder('FOS\UserBundle\Model\UserManagerInterface')->getMock();
        $email = 'test_email';
        $userRole = 'test_role';
        $user = new TestUser();
        $user->addRole($userRole);

        $userManagerMock->expects($this->exactly(2))
            ->method('findUserByEmail')
            ->will($this->returnValue($user))
            ->with($this->equalTo($email));

        $eventDispatcherMock = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
        $requestStackMock = $this->getRequestStackMock(false);

        $manipulator = new UserManipulator($userManagerMock, $eventDispatcherMock, $requestStackMock);

        $this->assertTrue($manipulator->removeRole($email, $userRole));
        $this->assertFalse($user->hasRole($userRole));
        $this->assertFalse($manipulator->removeRole($email, $userRole));
    }

    /**
     * @param string $event
     * @param bool   $once
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEventDispatcherMock($event, $once = true)
    {
        $eventDispatcherMock = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();

        $eventDispatcherMock->expects($once ? $this->once() : $this->never())
            ->method('dispatch')
            ->with($event);

        return $eventDispatcherMock;
    }

    /**
     * @param bool $once
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRequestStackMock($once = true)
    {
        $requestStackMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')->getMock();

        $requestStackMock->expects($once ? $this->once() : $this->never())
            ->method('getCurrentRequest')
            ->willReturn(null);

        return $requestStackMock;
    }
}
