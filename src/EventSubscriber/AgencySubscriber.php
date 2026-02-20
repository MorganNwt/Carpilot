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
        private Environment $twig
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

        // Sécurité : si ce n'est pas un controller standard
        if (!is_array($controller)) {
            return;
        }

        $controllerClass = get_class($controller[0]);

        //  On ignore les controllers API
        if (str_contains($controllerClass, '\\Controller\\Api\\')) {
            return;
        }

        // On récupère les agences depuis la BDD
        $agencyEntities = $this->agencyRepository->findAll();

        // On les rend disponibles globalement dans Twig
        $this->twig->addGlobal('agencyEntities', $agencyEntities);
    }
}
