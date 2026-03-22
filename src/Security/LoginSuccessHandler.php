<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    use TargetPathTrait;

    public function __construct(
        private RouterInterface $router,
        private JWTTokenManagerInterface $jwtManager
    ) {}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = $token->getUser();
        if ($user instanceof UserInterface) {
            $jwt = $this->jwtManager->create($user);
            $request->getSession()->set('token', $jwt);
        }

        // Priorité ABSOLUE : TargetPath (flux plaque → retour form estimation)
        // Firewall name : adapte si ton firewall ne s'appelle pas "main"
        $firewallName = 'main';

        if ($request->hasSession()) {
            $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
            if (is_string($targetPath) && str_starts_with($targetPath, '/')) {
                $this->removeTargetPath($request->getSession(), $firewallName);
                return new RedirectResponse($targetPath);
            }
        }

        // Priorité ensuite au redirect (POST puis GET)
        $redirect = $request->request->get('redirect') ?? $request->query->get('redirect');

        // Sécurité : uniquement chemins internes
        if (is_string($redirect) && str_starts_with($redirect, '/')) {
            return new RedirectResponse($redirect);
        }

        // Sinon → logique par rôle
        $roles = $token->getRoleNames();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            return new RedirectResponse($this->router->generate('admin_dashboard'));
        }

        if (in_array('ROLE_AGENT', $roles, true)) {
            return new RedirectResponse($this->router->generate('agent_dashboard'));
        }

        if (in_array('ROLE_SELLER', $roles, true)) {
            return new RedirectResponse($this->router->generate('seller_dashboard'));
        }

        // Par défaut → page d'accueil
        return new RedirectResponse($this->router->generate('app_home'));
    }
}
