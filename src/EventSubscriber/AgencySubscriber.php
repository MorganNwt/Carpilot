<?php

namespace App\EventSubscriber;

use App\Repository\AgencyRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class AgencySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AgencyRepository $agencyRepository,
        private Environment $twig,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onController',
        ];
    }

    public function onController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        $controllerClass = get_class($controller[0]);

        // Ignore les controllers API
        if (str_contains($controllerClass, '\\Controller\\Api\\')) {
            return;
        }

        //  Définir Valence par défaut si rien en session
        $request = $event->getRequest();
        if ($request->hasSession()) {
            $session = $request->getSession();

            if (!$session->has('selected_agency_id') || !$session->has('selected_agency_name')) {
                $valence = $this->agencyRepository->findOneBy(['name' => 'Valence']);

                if ($valence) {
                    $session->set('selected_agency_id', $valence->getId());
                    $session->set('selected_agency_name', $valence->getName());
                }
            }
        }

        // Agences globales Twig
        $agencyEntities = $this->agencyRepository->findAll();
        $this->twig->addGlobal('agencyEntities', $agencyEntities);
    }
}
