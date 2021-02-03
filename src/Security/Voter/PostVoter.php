<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class PostVoter extends Voter
{
    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['POST'])
            && $subject instanceof \App\Entity\Post;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        $post = $subject;
        // ... (check conditions and return true to grant permission) ...
        if($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if($this->security->isGranted('ROLE_VALID')) {
            switch ($attribute) {
                case 'POST':
                    return $post->getUser()->getId() === $user->getId();
                    break;
            }
        }

        return false;
    }
}
