<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{AssociationField,CollectionField,DateTimeField,FormField,ImageField,IntegerField,NumberField,TextEditorField,TextField};

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            FormField::addPanel('Basic information')->setIcon('fa fa-pencil')->setHelp('Phone number is preferred'),
            TextField::new('name'),
            NumberField::new('price'),
            TextEditorField::new('description'),

            FormField::addPanel('Other information')->setIcon('fa fa-th-list')->addCssClass(''),
            IntegerField::new('stock'),
            AssociationField::new('owner')->hideOnForm(),
            DateTimeField::new('createdAt')->onlyOnDetail(),
            AssociationField::new('tags'),//->setEntryType(TagType::class)->allowAdd(true)->allowDelete(true)->setEntryIsComplex(false),

            FormField::addPanel('Attachments')->setIcon('fa fa-paperclip')->addCssClass(''),
            ImageField::new('images')
                ->setUploadDir('public/uploads')->setBasePath('/uploads')
                ->setUploadedFileNamePattern('[year]-[month]-[day]-[contenthash].[extension]')
                ->setFormTypeOptions(['multiple' => true, 'allow_delete' => true]),
        ];

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
