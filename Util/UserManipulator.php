<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\UserBundle\Util;

use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Executes some manipulations on the users.
 *
 * @author Christophe Coevoet <stof@notk.org>
 * @author Luis Cordova <cordoval@gmail.com>
 */
class UserManipulator
{
    /**
     * User manager.
     *
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * UserManipulator constructor.
     *
     * @param UserManagerInterface     $userManager
     * @param EventDispatcherInterface $dispatcher
     * @param RequestStack             $requestStack
     */
    public function __construct(UserManagerInterface $userManager, EventDispatcherInterface $dispatcher, RequestStack $requestStack)
    {
        $this->userManager = $userManager;
        $this->dispatcher = $dispatcher;
        $this->requestStack = $requestStack;
    }

    /**
     * Creates a user and returns it.
     *
     * @param string $password
     * @param string $email
     * @param bool   $active
     * @param bool   $superadmin
     *
     * @return \FOS\UserBundle\Model\UserInterface
     */
    public function create($password, $email, $active, $superadmin)
    {
        $user = $this->userManager->createUser();
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->setEnabled((bool) $active);
        $user->setSuperAdmin((bool) $superadmin);
        $this->userManager->updateUser($user);

        $event = new UserEvent($user, $this->getRequest());
        $this->dispatcher->dispatch(FOSUserEvents::USER_CREATED, $event);

        return $user;
    }

    /**
     * Activates the given user.
     *
     * @param string $email
     */
    public function activate($email)
    {
        $user = $this->findUserByEmailOrThrowException($email);
        $user->setEnabled(true);
        $this->userManager->updateUser($user);

        $event = new UserEvent($user, $this->getRequest());
        $this->dispatcher->dispatch(FOSUserEvents::USER_ACTIVATED, $event);
    }

    /**
     * Deactivates the given user.
     *
     * @param string $email
     */
    public function deactivate($email)
    {
        $user = $this->findUserByEmailOrThrowException($email);
        $user->setEnabled(false);
        $this->userManager->updateUser($user);

        $event = new UserEvent($user, $this->getRequest());
        $this->dispatcher->dispatch(FOSUserEvents::USER_DEACTIVATED, $event);
    }

    /**
     * Changes the password for the given user.
     *
     * @param string $email
     * @param string $password
     */
    public function changePassword($email, $password)
    {
        $user = $this->findUserByEmailOrThrowException($email);
        $user->setPlainPassword($password);
        $this->userManager->updateUser($user);

        $event = new UserEvent($user, $this->getRequest());
        $this->dispatcher->dispatch(FOSUserEvents::USER_PASSWORD_CHANGED, $event);
    }

    /**
     * Adds role to the given user.
     *
     * @param string $email
     * @param string $role
     *
     * @return bool true if role was added, false if user already had the role
     */
    public function addRole($email, $role)
    {
        $user = $this->findUserByEmailOrThrowException($email);
        if ($user->hasRole($role)) {
            return false;
        }
        $user->addRole($role);
        $this->userManager->updateUser($user);

        return true;
    }

    /**
     * Removes role from the given user.
     *
     * @param string $email
     * @param string $role
     *
     * @return bool true if role was removed, false if user didn't have the role
     */
    public function removeRole($email, $role)
    {
        $user = $this->findUserByEmailOrThrowException($email);
        if (!$user->hasRole($role)) {
            return false;
        }
        $user->removeRole($role);
        $this->userManager->updateUser($user);

        return true;
    }

    /**
     * Finds a user by his email and throws an exception if we can't find it.
     *
     * @param string $email
     *
     * @throws \InvalidArgumentException When user does not exist
     *
     * @return UserInterface
     */
    private function findUserByEmailOrThrowException($email)
    {
        $user = $this->userManager->findUserByEmail($email);

        if (!$user) {
            throw new \InvalidArgumentException(sprintf('User identified by "%s" email does not exist.', $email));
        }

        return $user;
    }

    /**
     * @return Request
     */
    private function getRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }
}
