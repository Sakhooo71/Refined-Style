<?php

namespace App\Controller\Admin;

use App\Entity\CustomerOrder;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CustomerOrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CustomerOrder::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            DateTimeField::new('orderDate', 'Order Date'),
            TextField::new('orderNumber', 'Order Number'),
            TextField::new('orderStatus', 'Order Status'),
            NumberField::new('totalPrice', 'Total Price'),
            NumberField::new('totalQuantity', 'Total Quantity'),
            TextField::new('paymentMethod', 'Payment Method'),
            AssociationField::new('address', 'Address'),
            AssociationField::new('user', 'User'),
            AssociationField::new('cartItems', 'Cart Items')->hideOnForm(),
            AssociationField::new('orderItems', 'Order Items')->hideOnForm(),
        ];
    }
    
}
