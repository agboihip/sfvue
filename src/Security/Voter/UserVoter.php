<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class UserVoter extends Voter
{
    public const EDIT = 'PRODUCT_EDIT';
    public const VIEW = 'PRODUCT_VIEW';
    public const REMO = 'PRODUCT_DELL';

    public function __construct(private readonly Security $security){}

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::REMO]);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) return false;

        switch ($attribute) {
            case self::VIEW: return $this->canView($subject);
            case self::REMO: return $this->canView() || $this->canEdit($subject, $user);
            case self::EDIT: return $this->canEdit($subject, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canEdit($subject, User $user): bool
    {
        return $user === $subject;
    }

    private function canView(string $role = 'ROLE_ADMIN'): bool
    {
        return $this->security->isGranted($role);
    }
}
