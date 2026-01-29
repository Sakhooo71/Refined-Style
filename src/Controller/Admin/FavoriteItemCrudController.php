<?php

namespace App\Controller\Admin;

use App\Entity\FavoriteItem;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class FavoriteItemCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return FavoriteItem::class;
    }
    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('user', 'User'),
            AssociationField::new('product', 'Product'),
        ];
    }
    
}
