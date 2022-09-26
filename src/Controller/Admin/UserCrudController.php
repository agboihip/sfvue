<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Action,Actions,Crud};
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{ArrayField,
    BooleanField,
    ChoiceField,
    DateTimeField,
    EmailField,
    IdField,
    ImageField,
    TelephoneField,
    TextField};

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields =  [
            IdField::new('id')->onlyOnDetail(),
            TextField::new('type')->hideOnForm(),
            TextField::new('name'),
            TelephoneField::new('phone'),
            EmailField::new('email'),
            BooleanField::new('enabled')->hideOnForm(),
            DateTimeField::new('createdAt')->onlyOnDetail(),
            DateTimeField::new('lastLoginAt')->onlyOnDetail(),
            ChoiceField::new('roles')->allowMultipleChoices()
                ->renderAsBadges(['ROLE_ADMIN' => 'success', 'ROLE_MANAG' => 'warning'])
                ->setChoices(['Administrateur' => 'ROLE_ADMIN', 'Manager' => 'ROLE_MANAG']),
            ImageField::new('avatar')->setBasePath('/media/')
        ];
        //if ($pageName == Crud::PAGE_INDEX || $pageName == Crud::PAGE_DETAIL) $fields[] = ;
        //else$fields[] = ImageField::new('image')->setFormType(MediaType::class);

        return $fields;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE);
    }
}
