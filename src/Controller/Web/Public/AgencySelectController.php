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
 * This controller handles the selection of an agency by the user. When a user selects an agency, it stores the selected agency's ID and name 
 * in the session and redirects the user to the home page. This allows the application to remember the user's selected 
 * agency across different pages and sessions.
 * 
 */
class AgencySelectController extends AbstractController
{
    #[Route('/select-agency/{id}', name: 'web_public_select_agency', methods: ['POST'])]
    public function select(Agency $agency, Request $request): RedirectResponse
    {
        $request->getSession()->set('selected_agency_id', $agency->getId());
        $request->getSession()->set('selected_agency_name', $agency->getName());

        return $this->redirectToRoute('app_home');
    }
}
