<?php
declare(strict_types = 1);

namespace App\Event\Subscriber;

use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\{AuthenticationSuccessEvent,JWTCreatedEvent};
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\{JsonResponse,RequestStack,Response};
use Symfony\Component\Security\Http\Event\LogoutEvent;

class AuthSuccessSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestStack          $stack,
        private readonly UserRepository        $repository
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            Events::JWT_CREATED => 'onJWTCreated',
            LogoutEvent::class => 'onLogoutEvent',
            Events::AUTHENTICATION_SUCCESS => 'onAuthSuccess',
        ];
    }

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();
        $data = $event->getData();
        $clIp = $this->stack->getCurrentRequest()->getClientIp();

        if ($user instanceof User) {
            $event->setData([...$data, 'id' => $user->getId(), 'name' => $user->getName()]);
            if($clIp !== $user->getLastLoginIp()) $user->setLastLoginIp($clIp);
            $this->repository->save($user->setLastLoginAt(new \DateTimeImmutable()));
        }
    }

    public function onLogoutEvent(LogoutEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if(in_array('application/json', $request->getAcceptableContentTypes()))
            $response = new JsonResponse(null, Response::HTTP_NO_CONTENT);
        $response->headers->clearCookie('BEARER');//REFRESH_TOKEN

        $event->setResponse($response);
    }

    public function onAuthSuccess(AuthenticationSuccessEvent $event): void
    {
        $data = $event->getData();
        $user = $event->getUser();

        if ($user instanceof User)
            $event->setData(array_merge($data, ['user' => array(
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'phone' => $user->getPhone(),
                'avatar' => $user->getAvatar(),
                'lastLogin' => $user->getLastLoginAt(),
                'roles' => $user->getRoles(),
            )]));
    }
}