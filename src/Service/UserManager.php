<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Uid\Ulid;

class UserManager
{
    public function __construct(
        private readonly UserRepository              $repository,
        private readonly UserPasswordHasherInterface $encoder,
        private readonly TokenGeneratorInterface     $generator,
        private readonly UrlGeneratorInterface       $urlGenerator,
        private readonly MailerService               $mailer) {}

    public function add(User $u): ?User
    {
        return $this->repository->save($u);
    }

    public function del(User $u): ?User
    {
        return $this->repository->drop($u);
    }

    public function one(Ulid $u): ?User
    {
        return $this->repository->find($u);
    }

    public function load(string $username): ?User
    {
        return $this->repository->loadUserByIdentifier($username);
    }

    public function changePass(User $user, array $pass): ?User
    {
        if (!$this->encoder->isPasswordValid($user, $pass['old_password']))
            throw new AccessDeniedException();
        return $this->add($user->setPassword($this->encoder->hashPassword($user, $pass['new_password'])));
    }

    public function confirmUser(array $params): ?User
    {
        $user = $this->repository->findOneBy(['confirmaToken' => $params["token"]]);
        if (null === $user) throw new NotFoundHttpException();

        if ($params["password"] != null)
            $user->setPassword($this->encoder->hashPassword($user, $params["password"]));
        $user->setEnabled($user->isEnabled() ?? true);

        return $this->add($user->setConfirmaToken(null));
    }

    public function registerUser(User $user): ?User
    {
        $user->setPassword($this->encoder->hashPassword($user, $user->getPassword()));
        $user = $this->add($user->setConfirmaToken($this->generator->generateToken())); //base64_encode(\random_bytes(30));

        $expiredAt = (new \DateTime())->add(new \DateInterval('PT30M'));
        $this->mailer->send(
            new Address('agbohippolyte@gmail.com', 'Bai Admin Contact'),
            'Please Confirm your Email',
            $user->getEmail(),
            'security/confirmation_email.html.twig',
            [
                'validUntil' => $expiredAt,
                'signedUrl' => $this->urlGenerator->generate(
                    'app_verify_email',
                    ['token' => $user->getConfirmaToken()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            ]
        );

        return $user;
    }
}