<?php

namespace App\Controller\Admin;

use App\Entity\Address;
use App\Entity\CartItem;
use App\Entity\Category;
use App\Entity\Contact;
use App\Entity\CustomerOrder;
use App\Entity\FavoriteItem;
use App\Entity\Offer;
use App\Entity\OrderAddress;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
         return $this->render('dashboard/admin_dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
        ->setTitle('<img src="/images/icon.svg" width="50"> Refined Style')
        ->setFaviconPath('/images/icon.svg')
        ->renderContentMaximized()
        ->setLocales([
            'en' => '🇬🇧 English',
            'fr' => '🇫🇷 Français'
        ]);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::linkToCrud('Address', 'fas fa-map-marker-alt', Address::class);
        yield MenuItem::linkToCrud('Cart Items', 'fas fa-shopping-cart', CartItem::class);
        yield MenuItem::linkToCrud('Category', 'fas fa-tags', Category::class);
        yield MenuItem::linkToCrud('Contact', 'fas fa-envelope', Contact::class);
        yield MenuItem::linkToCrud('Customer Orders', 'fas fa-shopping-basket', CustomerOrder::class);
        yield MenuItem::linkToCrud('Favorite Items', 'fas fa-heart', FavoriteItem::class);
        yield MenuItem::linkToCrud('Offer', 'fas fa-gift', Offer::class);
        yield MenuItem::linkToCrud('Order Address', 'fas fa-address-book', OrderAddress::class);
        yield MenuItem::linkToCrud('Order Items', 'fas fa-boxes', OrderItem::class);
        yield MenuItem::linkToCrud('Product', 'fas fa-box', Product::class);
        yield MenuItem::linkToCrud('User', 'fas fa-users', User::class);

        yield MenuItem::linkToRoute('Back to Shop', 'fas fa-arrow-left', 'app_default');
        yield MenuItem::linkToRoute('My Orders', 'fas fa-file-alt', 'user_orders');
    }


    
   
}
