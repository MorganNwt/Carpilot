<?php

namespace App\Controller\Web\Public;

use App\Entity\Agency;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Summary of AgencySelectController
 *
 * This controller handles the selection of an agency by the user.
 */
class AgencySelectController extends AbstractController
{
    #[Route('/select-agency/{id}', name: 'web_public_select_agency', methods: ['POST', 'GET'])]
    public function select(Agency $agency, Request $request): RedirectResponse
    {
        $session = $request->getSession();

        // Mise à jour de l'agence sélectionnée
        $session->set('selected_agency_id', $agency->getId());
        $session->set('selected_agency_name', $agency->getName());

        // Retour sur la page précédente
        $referer = $request->headers->get('referer');

        if ($referer) {
            return $this->redirect($referer);
        }

        // Fallback selon le rôle connecté
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_admin_dashboard');
        }

        // Redirection par défaut pour les autres rôles
        if ($this->isGranted('ROLE_AGENT')) {
            return $this->redirectToRoute('app_agent_dashboard');
        }

        // Redirection par défaut pour les vendeurs
        if ($this->isGranted('ROLE_SELLER')) {
            return $this->redirectToRoute('app_seller_dashboard');
        }

        // Redirection par défaut pour les autres utilisateurs
        return $this->redirectToRoute('app_home');
    }
}
