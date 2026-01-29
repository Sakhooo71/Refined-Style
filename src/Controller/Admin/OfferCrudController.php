<?php

namespace App\Controller\Admin;

use App\Entity\Offer;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class OfferCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Offer::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('type', 'Offer Type')->setRequired(false),
            NumberField::new('value', 'Offer Value')->setRequired(false),
            DateTimeField::new('startDate', 'Start Date')->setRequired(false),
            DateTimeField::new('endDate', 'End Date')->setRequired(false),
            AssociationField::new('products', 'Products')->hideOnForm(),
        ];
    }
    
}
