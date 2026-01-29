<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('categoryName', 'Category Name')
                ->setRequired(false), // Champ texte pour le nom de la catégorie
            
            ImageField::new('categoryImage', 'Category Image')
                ->setUploadDir('public/uploads/categories') // Répertoire de téléchargement
                ->setBasePath('/uploads/categories') // Chemin de base pour accéder à l'image
                ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]') // Génération d'un nom de fichier unique
                ->setRequired(false), // Le champ image est optionnel
            
            AssociationField::new('products', 'Products')
                ->hideOnForm(), // Champ d'association pour les produits liés, caché dans les formulaires d'ajout/édition
        ];
    }
}
