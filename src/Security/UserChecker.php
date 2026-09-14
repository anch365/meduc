<?php

namespace App\Security;

use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        // Rien à vérifier AVANT l'authentification
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        if (!$user instanceof Utilisateur) {
            return;
        }

        // Si le compte est inactif → on REFUSE avec un message clair
        if (!$user->isActif()) {
            throw new CustomUserMessageAccountStatusException(
                'Ce compte a été désactivé. Contactez l\'administration.'
            );
        }
    }
}