<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
         return [
            TextField::new('productName', 'Nom du produit'),
            MoneyField::new('productPrice', 'Prix')->setCurrency('EUR'),
            AssociationField::new('category', 'Catégorie'),
            TextareaField::new('shortDescription', 'Description courte'),
            TextareaField::new('longDescription', 'Description longue'),
            TextField::new('productBrand', 'Marque'),
            TextField::new('productOrigin', 'Origine'),
            ImageField::new('productImage', 'Image')
                ->setBasePath('/uploads/products') // Chemin d'accès public aux images
                ->setUploadDir('public/uploads/products') // Chemin physique où stocker les images
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),
        ];
    }
    
}
