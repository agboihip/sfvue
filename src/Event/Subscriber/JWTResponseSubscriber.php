<?php

namespace App\Event\Subscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\{JsonResponse,RequestStack,RedirectResponse,Response};
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class JWTResponseSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestStack $stack,
        private readonly UrlGeneratorInterface $generator
    ) {}

    public static function getSubscribedEvents()
    {
        return array(
            Events::JWT_NOT_FOUND => 'onJWTNotFound',
            //Events::AUTHENTICATION_FAILURE => 'onJWTNotFound',
        );
    }


    public function onJWTNotFound(JWTNotFoundEvent $event): Response
    {
        $request = $this->stack->getCurrentRequest(); //$response = $event->getResponse();
        if ($request->cookies->get('BEARER')) {
            $response = new JsonResponse([
                'status'  => Response::HTTP_UNAUTHORIZED . ' Unauthorized',
                'message' => 'Expired token',
            ], 401);

            $event->setResponse($response);
            return $response;
        }
        $response = in_array('text/html', $request->getAcceptableContentTypes()) ?
            new RedirectResponse($this->generator->generate('app_login'), Response::HTTP_FOUND) :
            new JsonResponse(['status'  => Response::HTTP_FORBIDDEN . ' Forbidden', 'message' => 'Missing token',], Response::HTTP_UNAUTHORIZED);

        $event->setResponse($response);
        return $response;
    }
}