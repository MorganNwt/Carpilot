<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        // ✅ Aucune logique de sécurité ici
        // 👉 le contrôle d’accès se fait en JS via le JWT (localStorage)

        return $this->render('account/account.html.twig');
    }
}
