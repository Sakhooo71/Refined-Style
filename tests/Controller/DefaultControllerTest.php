<?php

namespace App\Tests\Controller;

use App\Entity\Product;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class DefaultControllerTest extends WebTestCase
{
    public function testProductDetailsAndReviewSubmission(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

        // Create a user
        $user = new User();
        $user->setEmail('testuser@example.com');
        $user->setPassword('password');
        $user->setFirstName('Test');
        $user->setLastName('User');
        $entityManager->persist($user);

        // Create a product
        $product = new Product();
        $product->setProductName('Test Product');
        $product->setProductPrice('10.00');
        $product->setProductImage('images/product1.jpg');
        $product->setShortDescription('Short description');
        $product->setLongDescription('Long description');
        $product->setProductBrand('Brand');
        $product->setProductOrigin('Origin');
        $entityManager->persist($product);
        $entityManager->flush();

        // Log in as the user
        $client->loginUser($user);

        // Go to the product page
        $crawler = $client->request('GET', '/product/' . $product->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Test Product');

        // Submit a review
        $form = $crawler->selectButton('Submit')->form();
        $form['review[rating]'] = 5;
        $form['review[comment]'] = 'This is a great product!';
        $client->submit($form);

        // Follow the redirect
        $crawler = $client->followRedirect();

        // Check that the review is displayed
        $this->assertSelectorTextContains('.card-title', 'Rating: 5/5');
        $this->assertSelectorTextContains('.card-text', 'This is a great product!');

        // Check that the review is in the database
        $reviewRepository = $entityManager->getRepository(\App\Entity\Review::class);
        $review = $reviewRepository->findOneBy(['product' => $product, 'user' => $user]);
        $this->assertNotNull($review);
        $this->assertSame(5, $review->getRating());
        $this->assertSame('This is a great product!', $review->getComment());
    }
}
