<?php

namespace App\Security\Voter;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class CommentVoter extends Voter
{
    public function __construct(Security $security, PostRepository $postRepository)
    {
        $this->security = $security;
        $this->postRepository = $postRepository;
    }
    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['COMMENT'])
            && $subject instanceof \App\Entity\Comment;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        $comment = $subject;

        // ... (check conditions and return true to grant permission) ...
        if($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if($this->security->isGranted('ROLE_VALID')) {
            switch ($attribute) {
                case 'COMMENT':
                    return in_array($comment->getPost(), $this->postRepository->findBy(['user' => $user->getId()]));
                    break;
            }
        }

        return false;
    }
}
