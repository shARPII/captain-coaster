<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Image::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Picture')
            ->setEntityLabelInPlural('Pictures')
            ->setSearchFields(['id', 'coaster.name', 'uploader.username', 'filename', 'credit'])
            ->setDefaultSort(['enabled' => 'ASC', 'updatedAt' => 'DESC'])
            ->showEntityActionsInlined()
            ->setPaginatorPageSize(20);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->disable('new');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('uploader'),
            AssociationField::new('coaster'),
            TextField::new('credit'),
            ImageField::new('path', 'Image')->setBasePath('/images/coasters/')->onlyOnIndex(),
            BooleanField::new('enabled'),
            BooleanField::new('watermarked')->onlyWhenUpdating()->setFormTypeOption('disabled', 'disabled'),
            BooleanField::new('optimized')->onlyWhenUpdating()->setFormTypeOption('disabled', 'disabled'),
            IntegerField::new('likeCounter')->hideOnIndex(),
            DateTimeField::new('createdAt')->onlyWhenUpdating()->setFormTypeOption('disabled', 'disabled'),
            DateTimeField::new('updatedAt')->onlyWhenUpdating()->setFormTypeOption('disabled', 'disabled'),

        ];
    }
}
