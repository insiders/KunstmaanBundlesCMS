<?php

namespace Kunstmaan\AdminBundle\EventListener;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Model\UserManager;

/**
 * Set password_changed property to 1 after changing the password
 */
class PasswordResettingListener
{
    /**
     * @var UserManager
     */
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function onPasswordResettingSuccess(FilterUserResponseEvent $event)
    {
        $user = $event->getUser();
        $user->setPasswordChanged(true);
        $this->userManager->updateUser($user);
    }
}
