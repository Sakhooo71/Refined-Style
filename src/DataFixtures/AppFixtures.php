<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Product;
use App\Entity\Address;
use App\Entity\CartItem;
use App\Entity\Category;
use App\Entity\CustomerOrder;
use App\Entity\FavoriteItem;
use App\Entity\OrderItem;
use App\Entity\Offer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR'); // French locale for more realism
        $picturePaths = ['/uploads/products', '/uploads/categories'];
        
        // --- Create Admin User ---
        $admin = new User();
        $admin->setEmail('admin@admin.fr')
              ->setRoles(['ROLE_ADMIN'])
              ->setPassword('$2y$13$wdMwb4jhk89Y/K4as.MYDOSPhldsE/rS6nBEpinowl3QivVutJcpa')
              ->setFirstName('admin')
              ->setLastName('luc');
        $manager->persist($admin);

        // --- Create Users ---
        $users = [];
        for ($i = 0; $i < 50; $i++) {
            $user = new User();
            $user->setFirstname($faker->firstName)
                 ->setLastname($faker->lastName)
                 ->setEmail('host' . $i . '@host.fr')
                 ->setPassword($this->passwordHasher->hashPassword($user, 'leMotDePasse')) // Pre-hashed password
                 ->setRoles(['ROLE_CUSTOMER']);
            $manager->persist($user);
            $users[] = $user;
        }

        // --- Create Addresses ---
        for ($i = 0; $i < 10; $i++) {
            $address = new Address();
            $address->setFirstName($faker->firstName)
                    ->setLastName($faker->lastName)
                    ->setCity($faker->city)
                    ->setPostalCode($faker->postcode)
                    ->setStreetAddress($faker->streetAddress)
                    ->setAddressComplement($faker->secondaryAddress)
                    ->setPhoneNumber($faker->phoneNumber)
                    ->setUser($users[array_rand($users)]);
            $manager->persist($address);
        }

        // --- Create Categories ---
        $categories = [];
        $luxuryCategories = [
            'Sacs à main', 
            'Montres de luxe', 
            'Vêtements de créateurs', 
            'Lunettes de soleil', 
            'Chaussures de luxe',
            'Parfums de luxe'
        ];
        
        for ($i = 0; $i < count($luxuryCategories); $i++) {
            $category = new Category();
            $category->setCategoryName($luxuryCategories[$i])
                     ->setCategoryImage('uploads/categories/' . strtolower(str_replace(' ', '_', $luxuryCategories[$i])) . '.jpg'); // Nom d'image spécifique
            $manager->persist($category);
            $categories[] = $category;
        }

        // --- Create Products ---
// --- Create Products ---
       // --- Create Products ---
$luxuryProducts = ['Sac', 'Vêtement', 'Lunettes', 'Montre', 'Chaussures', 'Parfum', 'Ceinture', 'Portefeuille'];
$luxuryBrands = ['Louis Vuitton', 'Gucci', 'Chanel', 'Hermès', 'Prada', 'Dior', 'Balenciaga', 'Versace', 'Givenchy', 'Yves Saint Laurent'];

$products = [];
for ($i = 0; $i < 100; $i++) {
    $productType = $faker->randomElement($luxuryProducts);
    $brand = $faker->randomElement($luxuryBrands);
    $productName = "$productType $brand"; // Ex: "Sac Louis Vuitton"

    $product = new Product();
    $product->setProductName($productName)
            ->setProductPrice($faker->randomFloat(2, 500, 5000)) // Prix de 500 à 5000 pour des articles de luxe
            ->setShortDescription("Un $productType de la marque $brand")
            ->setLongDescription("Un magnifique $productType conçu par $brand, idéal pour toutes les occasions de luxe.")
            ->setProductBrand($brand)
            ->setProductOrigin($faker->country)
            ->setProductImage('images/product' . $faker->numberBetween(1, 3) . '.jpg')
            ->setCategory($categories[array_rand($categories)]);
    $manager->persist($product);
    $products[] = $product;
}


        // --- Create Customer Orders ---
        $customerOrders = [];
        for ($i = 0; $i < 10; $i++) {
            $customerOrder = new CustomerOrder();
            $customerOrder->setOrderDate($faker->dateTimeThisYear)
                          ->setOrderNumber($faker->uuid)
                          ->setOrderStatus($faker->randomElement(['pending', 'shipped', 'delivered']))
                          ->setTotalPrice($faker->randomFloat(2, 20, 200))
                          ->setTotalQuantity($faker->numberBetween(1, 10))
                          ->setPaymentMethod($faker->randomElement(['cash', 'card', 'mobile money']))
                          ->setUser($users[array_rand($users)]);
            $manager->persist($customerOrder);
            $customerOrders[] = $customerOrder;
        }

        // --- Create Cart Items ---
        for ($i = 0; $i < 20; $i++) {
            $cartItem = new CartItem();
            $cartItem->setQuantity($faker->numberBetween(1, 10))
                     ->setDiscount($faker->randomFloat(2, 0, 50))
                     ->setProduct($products[array_rand($products)])
                     ->setCustomerOrder($customerOrders[array_rand($customerOrders)])
                     ->setUser($users[array_rand($users)]);
            $manager->persist($cartItem);
        }

        // --- Create Favorite Items ---
        for ($i = 0; $i < 10; $i++) {
            $favoriteItem = new FavoriteItem();
            $favoriteItem->setUser($users[array_rand($users)])
                         ->setProduct($products[array_rand($products)]);
            $manager->persist($favoriteItem);
        }

        // --- Create Offers ---
        for ($i = 0; $i < 5; $i++) {
            $offer = new Offer();
            $offer->setType($faker->randomElement(['percentage', 'fixed']))
                  ->setValue($faker->randomFloat(2, 5, 30))
                  ->setStartDate($faker->dateTimeThisYear)
                  ->setEndDate($faker->dateTimeThisYear);
            $manager->persist($offer);
        }

        // --- Create Order Items ---
        for ($i = 0; $i < 20; $i++) {
            $product = $products[array_rand($products)];
            $orderItem = new OrderItem();
            $orderItem->setQuantity($faker->numberBetween(1, 10))
                      ->setPrice($faker->randomFloat(2, 10, 100))
                      ->setDiscountedPrice($faker->randomFloat(2, 5, 80))
                      ->setProduct($product)
                      ->setCustomerOrder($customerOrders[array_rand($customerOrders)])
                      ->setProductName($product->getProductName());
            $manager->persist($orderItem);
        }

        // --- Flush all changes to the database ---
        $manager->flush();
    }
}
