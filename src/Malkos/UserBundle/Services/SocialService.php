<?php

namespace Malkos\UserBundle\Services;

class SocialService
{

    private $userManager;

    public function __construct($userManager)
    {
        $this->userManager = $userManager;
    }

    public function getServiceFields($serviceName, &$setter_id, &$setter_token)
    {
        $service = $serviceName;
        $setter = 'set' . ucfirst($service);
        $setter_id = $setter . 'Id';
        $setter_token = $setter . 'AccessToken';
    }

    public function createUser(array $data, $user = null)
    {
        $newUser = false;
        if (!$user) {
            $user = $this->userManager->createUser();
            $newUser = true;
        }
        $setter_id    = null;
        $setter_token = null;

        $this->getServiceFields($data['service_service'], $setter_id, $setter_token);

        $user->$setter_id($data['service_id']);
        $user->$setter_token($data['service_token']);

        if ($newUser) {
            $user->setUsername($data['username']);
            $user->setEmail($data['email']);
            $user->setPassword($data['service_id']);
            $user->setEnabled(true);
        }
        $this->userManager->updateUser($user);

        return $user;
    }
} 