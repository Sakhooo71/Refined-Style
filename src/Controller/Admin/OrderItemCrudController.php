<?php

namespace App\Controller\Admin;

use App\Entity\OrderItem;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class OrderItemCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return OrderItem::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            NumberField::new('quantity', 'Quantity'),
            NumberField::new('price', 'Price'),
            NumberField::new('discountedPrice', 'Discounted Price'),
            TextField::new('productName', 'Product Name'),
            AssociationField::new('cartItem', 'Cart Item')->hideOnForm(),
            AssociationField::new('product', 'Product'),
            AssociationField::new('customerOrder', 'Customer Order')->hideOnForm(),
        ];
    }
    
}
