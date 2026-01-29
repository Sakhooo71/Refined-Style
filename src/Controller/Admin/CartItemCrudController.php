<?php

namespace App\Controller\Admin;

use App\Entity\CartItem;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CartItemCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CartItem::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            NumberField::new('quantity', 'Quantity'),
            NumberField::new('discount', 'Discount')->setRequired(false),
            AssociationField::new('user', 'User'),
            AssociationField::new('product', 'Product'),
            AssociationField::new('customerOrder', 'Customer Order')->hideOnForm(),
            AssociationField::new('orderItems', 'Order Items')->hideOnForm(),
        ];
    }
    
}
