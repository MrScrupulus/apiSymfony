<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Subscriber pour intercepter et formater toutes les exceptions de l'application
 * 
 * Ce subscriber permet de :
 * - Intercepter les exceptions HTTP (404, 500, etc.)
 * - Retourner des réponses JSON formatées au lieu des pages d'erreur HTML
 * - Personnaliser les messages d'erreur pour l'API
 */
class ExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * Intercepte les exceptions et les convertit en réponses JSON
     * 
     * @param ExceptionEvent $event Événement d'exception
     */
    public function onExceptionEvent(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Gestion spécifique des erreurs 404 (Not Found)
        if ($exception instanceof NotFoundHttpException) {
            $data = [
                'status' => 404,
                'message' => $exception->getMessage(),
                
            ];
            $event->setResponse(new JsonResponse($data, 404));
        }
        // Gestion des autres exceptions HTTP (400, 401, 403, 500, etc.)
        elseif ($exception instanceof HttpException) {
            $data = [
                'status' => $exception->getStatusCode(),
                'message' => $exception->getMessage(),
                'error' => 'HTTP Exception'
            ];
            $event->setResponse(new JsonResponse($data, $exception->getStatusCode()));
        }
       
        else {
            $data = [
                'status' => 500,
                'message' => $exception->getMessage(),
                'error' => 'Internal Server Error',
                'details' => 'An unexpected error occurred'
            ];
            $event->setResponse(new JsonResponse($data, 500));
        }
    }

    /**
     * Définit les événements auxquels ce subscriber s'abonne
     * 
     * @return array Liste des événements et méthodes associées
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => 'onExceptionEvent',
        ];
    }
}