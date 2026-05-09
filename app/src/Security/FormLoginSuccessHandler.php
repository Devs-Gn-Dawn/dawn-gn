<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class FormLoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return new RedirectResponse($this->urlGenerator->generate('app_character_index'));
        }

        if ($user->isAdmin()) {
            return new RedirectResponse($this->urlGenerator->generate('app_admin'));
        }

        if ($user->isOrga()) {
            return new RedirectResponse($this->urlGenerator->generate('app_orga'));
        }

        return new RedirectResponse($this->urlGenerator->generate('app_character_index'));
    }
}
