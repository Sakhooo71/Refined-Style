<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(), // Cache l'ID dans les formulaires de création/édition

            EmailField::new('email', 'Email')
                ->setRequired(true), // Champ pour l'email

            ArrayField::new('roles', 'Roles')
                ->setRequired(true), // Champ pour les rôles

            TextField::new('firstName', 'First Name')
                ->setRequired(true), // Champ pour le prénom de l'utilisateur

            TextField::new('lastName', 'Last Name')
                ->setRequired(true), // Champ pour le nom de famille de l'utilisateur


            AssociationField::new('cartItems', 'Cart Items')
                ->hideOnForm(), // Cache la relation avec les éléments du panier dans le formulaire

            AssociationField::new('addresses', 'Addresses')
                ->hideOnForm(), // Cache la relation avec les adresses dans le formulaire

            AssociationField::new('customerOrders', 'Customer Orders')
                ->hideOnForm(), // Cache la relation avec les commandes dans le formulaire

            AssociationField::new('favoriteItems', 'Favorite Items')
                ->hideOnForm(), // Cache la relation avec les articles favoris dans le formulaire
        ];
    }
}
