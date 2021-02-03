<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

class AdminVoter extends Voter
{
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['USER', 'ADMIN', 'AUTH']);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        $trustResolver = new AuthenticationTrustResolver();
        $authenticatedVoter = new AuthenticatedVoter($trustResolver);
        $isAuth = $authenticatedVoter->vote($token, null, ['IS_AUTHENTICATED_FULLY']);

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case 'USER':
                return $this->security->isGranted('ROLE_VALID');
                break;
            case 'ADMIN':
                return $this->security->isGranted('ROLE_ADMIN');
                break;
            case 'AUTH':
                return $isAuth;
                break;
        }

        return false;
    }
}
