<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\UserBundle\Form\DataTransformer;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Transforms between a UserInterface instance and a email string.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class UserToEmailTransformer implements DataTransformerInterface
{
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * UserToEmailTransformer constructor.
     *
     * @param UserManagerInterface $userManager
     */
    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * Transforms a UserInterface instance into a email string.
     *
     * @param UserInterface|null $value UserInterface instance
     *
     * @return string|null Email
     *
     * @throws UnexpectedTypeException if the given value is not a UserInterface instance
     */
    public function transform($value)
    {
        if (null === $value) {
            return;
        }

        if (!$value instanceof UserInterface) {
            throw new UnexpectedTypeException($value, 'FOS\UserBundle\Model\UserInterface');
        }

        return $value->getEmail();
    }

    /**
     * Transforms a email string into a UserInterface instance.
     *
     * @param string $value Email
     *
     * @return UserInterface the corresponding UserInterface instance
     *
     * @throws UnexpectedTypeException if the given value is not a string
     */
    public function reverseTransform($value)
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        return $this->userManager->findUserByEmail($value);
    }
}
