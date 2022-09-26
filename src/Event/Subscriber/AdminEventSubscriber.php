<?php

namespace App\Event\Subscriber;

use App\Entity\{Model\Supplier, Product, User};
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;

final class AdminEventSubscriber implements EventSubscriberInterface
{
    private ?User $user;

    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
    }

    public static function getSubscribedEvents()
    {
        return array(
            BeforeEntityPersistedEvent::class => ['setUser'],
        );
    }

    public function setUser(BeforeEntityPersistedEvent  $event)
    {
        $entity = $event->getEntityInstance();
        if ($this->user instanceof Supplier && $entity instanceof Product) $entity->setOwner($this->user->getEshop());
    }
}