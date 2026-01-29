<?php

namespace App\Controller\Admin;

use App\Entity\Address;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class AddressCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Address::class;
    }

    
    public function configureFields(string $pageName): iterable
    { 
        return [
        TextField::new('firstName', 'First Name'),
        TextField::new('lastName', 'Last Name'),
        TextField::new('city', 'City'),
        TextField::new('postalCode', 'Postal Code'),
        TextField::new('streetAddress', 'Street Address'),
        TextField::new('addressComplement', 'Address Complement')->hideOnIndex(),
        TextField::new('phoneNumber', 'Phone Number'),
        AssociationField::new('user', 'User'),
        AssociationField::new('customerOrders', 'Customer Orders')->hideOnForm(),
    ];
    }
    
}
