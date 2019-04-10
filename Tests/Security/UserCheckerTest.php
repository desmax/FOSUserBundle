<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\UserBundle\Tests\Security;

use FOS\UserBundle\Security\UserChecker;
use PHPUnit\Framework\TestCase;

class UserCheckerTest extends TestCase
{
    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\DisabledException
     * @expectedExceptionMessage User account is disabled.
     */
    public function testCheckPreAuthFailsIsEnabled()
    {
        $userMock = $this->getUser(false);
        $checker = new UserChecker();
        $checker->checkPreAuth($userMock);
    }

    private function getUser($isEnabled)
    {
        $userMock = $this->getMockBuilder('FOS\UserBundle\Model\User')->getMock();
        $userMock
            ->method('isEnabled')
            ->willReturn($isEnabled);

        return $userMock;
    }
}
