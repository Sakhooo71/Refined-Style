<?php

namespace App\Controller;


use App\Twig\CartExtension;
use App\Form\UserProfileType;
use App\Service\OrderService;
use App\Service\PaymentService;
use App\Repository\AddressRepository;
use App\Repository\CartItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CustomerOrderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class CustomerOrderController extends AbstractController
{
    private $orderService;
    private $paymentService;

    public function __construct(OrderService $orderService, PaymentService $paymentService)
    {
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    #[Route('/process-and-create-session', name: 'app_process_and_create_session', methods: ['POST'])]
    public function processAndCreateSession(Request $request): Response
    {
         // Get the current user
         $user = $this->getUser();
         if (!$user) {
             return $this->redirectToRoute('app_login');
         }
 
         // Get selected address and payment method from the form
         $selectedAddressId = $request->request->get('selectedAddress');
         $selectedPaymentMethod = $request->request->get('paymentMethod');
 
         // Validate the payment method
         if (!in_array($selectedPaymentMethod, ['Stripe', 'PayPal'])) {
             $this->addFlash('error', 'Invalid payment method selected!');
             return $this->redirectToRoute('app_customer_order');
         }
 
         // Create the order and empty the cart
         $order = $this->orderService->createOrder($user, $selectedAddressId, $selectedPaymentMethod);
 
         // Store the order ID in session
         $session = $request->getSession();
         $session->set('order_id', $order->getId());
 
         // Handle payment
         if ($selectedPaymentMethod === 'Stripe') {
             return $this->createStripeSession($request);
         } elseif ($selectedPaymentMethod === 'PayPal') {
             return $this->createPaypalSession($request);
         }
 
         return $this->redirectToRoute('app_customer_order');
    }

    #[Route('/create-stripe-session', name: 'create_stripe_session', methods: ['POST'])]
    public function createStripeSession(Request $request): Response
    {
        $session = $request->getSession();
        $orderId = $session->get('order_id');
        if (!$orderId) {
            throw new \Exception('Order ID not found in session.');
        }

        // Parse the JSON content of the request
        $data = json_decode($request->getContent(), true);
        $totalPrice = $data['totalPrice'] ?? null;  // Get the total price from the JSON payload

        // Pass the total price as a parameter to createStripeSession
        $checkout_session = $this->paymentService->createStripeSession($orderId, $totalPrice);

        return new JsonResponse(['id' => $checkout_session->id]);
    }

    #[Route('/create-paypal-session', name: 'create_paypal_session', methods: ['POST'])]
    public function createPaypalSession(Request $request): Response
    {
        $session = $request->getSession();
        $orderId = $session->get('order_id');
        if (!$orderId) {
            throw new \Exception('Order ID not found in session.');
        }

        
        $data = json_decode($request->getContent(), true);
        $totalPrice = $data['totalPrice'] ?? null;

        
        $paypalSessionId = $this->paymentService->createPaypalSession($orderId, $totalPrice);

        return new JsonResponse(['id' => $paypalSessionId]);
    }

    #[Route('/customer/order', name: 'app_customer_order')]
    public function index(AddressRepository $addressRepository, CartExtension $cartExtension): Response
    {
        $user = $this->getUser();
        $totalPrice = $cartExtension->getTotalPrice(); 

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $addresses = $addressRepository->findBy(['user' => $user]);

        return $this->render('customer_order/index.html.twig', [
            'addresses' => $addresses,
            'total_price' => $totalPrice, 
            
        ]);    }

    #[Route('/process-order', name: 'app_process_order', methods: ['POST'])]
    public function processOrder(Request $request)
    {
// Log request data for debugging
error_log(print_r($request->request->all(), true));

// Get the current user
$user = $this->getUser();
if (!$user) {
    return $this->redirectToRoute('app_login');
}

// Get selected address and payment method from the form
$selectedAddressId = $request->request->get('selectedAddress');
error_log("Selected Address ID: " . $selectedAddressId);
$selectedPaymentMethod = $request->request->get('paymentMethod');

// Validate the payment method
if (!in_array($selectedPaymentMethod, ['Stripe', 'PayPal'])) {
    $this->addFlash('error', 'Invalid payment method selected!');
    return $this->redirectToRoute('app_customer_order');
}

// Create the order and empty the cart
$order = $this->orderService->createOrder($user, $selectedAddressId, $selectedPaymentMethod);

// Store the order ID in session
$session = $request->getSession();
$session->set('order_id', $order->getId());

// Handle payment
if ($selectedPaymentMethod === 'Stripe') {
    return $this->createStripeSession($request);
} elseif ($selectedPaymentMethod === 'PayPal') {
    return $this->createPaypalSession($request);
}

return $this->redirectToRoute('app_customer_order');
    }

    #[Route('/stripe-webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function stripeWebhook(Request $request, EntityManagerInterface $em, CartItemRepository $cartItemRepository, CustomerOrderRepository $customerOrderRepository)
    {
        \Stripe\Stripe::setApiKey($_ENV["STRIPE_API_KEY_SECRET"]);

        $payload = $request->getContent();
        $sig_header = $request->headers->get('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $_ENV["STRIPE_WEBHOOK_SECRET"]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $orderId = $session->metadata->order_id;

            $order = $customerOrderRepository->find($orderId);

            if ($order) {
                $order->setOrderStatus('Paid');  

                // Empty the cart
                $user = $order->getUser();
                $cartItems = $cartItemRepository->findBy(['user' => $user]);
                foreach ($cartItems as $cartItem) {
                    $em->remove($cartItem);
                }

                $em->persist($order);
                $em->flush();

                return new JsonResponse(['success' => 'Order processed successfully']);
            } else {
                return new JsonResponse(['error' => 'Order not found'], 404);
            }
        }

        return new JsonResponse(['success' => 'Webhook received']);
    }

    #[Route('/change-order-status-to-paid', name: 'change_order_status_to_paid', methods: ['POST'])]
    public function changeOrderStatusToPaid(Request $request, CustomerOrderRepository $customerOrderRepository, EntityManagerInterface $em)
    {
        $data = json_decode($request->getContent(), true);
        $orderId = $data['orderId'] ?? null;
        $status = $data['status'] ?? null;

        if ($orderId === null || $status === null) {
            return new JsonResponse(['error' => 'Invalid request'], 400);
        }

        $order = $customerOrderRepository->find($orderId);

        if (!$order) {
            return new JsonResponse(['error' => 'Order not found'], 404);
        }

        // Update the order status
        $order->setOrderStatus($status);

        // Persist the updated order status to the database
        $em->persist($order);
        $em->flush();

        return new JsonResponse(['success' => 'Order status updated successfully']);
    }

    #[Route('/order-confirmation/{orderId}', name: 'app_order_confirmation')]
    public function orderConfirmation($orderId, CustomerOrderRepository $customerOrderRepository, EntityManagerInterface $em)
    {
        $user = $this->getUser();
        $order = $customerOrderRepository->find($orderId);

        // START: Code to remove when using Webhook
        // Change the order status to 'Paid'
        // Note: Be cautious when using this approach. It is not a robust way to verify payment.
        // It should only be used for testing and development purposes.
        $order->setOrderStatus('Paid');
        $em->persist($order);
        $em->flush();
        // END: Code to remove when using Webhook
        $this->orderService->emptyCart($user);

        if (!$order) {
            throw $this->createNotFoundException('Order not found');
        }

        return $this->render('customer_order/confirmation.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/orders/show', name: 'user_orders')]
    public function showOrders(CustomerOrderRepository $orderRepo): Response
    {
        $user = $this->getUser();
        $orders = $orderRepo->findOrdersByUser($user);

        return $this->render('customer_order/user_orders.html.twig', [
            'orders' => $orders
        ]);
    }

    #[Route('/customer/order/{orderId}/details', name: 'app_customer_order_details')]
    public function orderDetails($orderId, CustomerOrderRepository $orderRepository)
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $order = $orderRepository->find($orderId);

        if (!$order || $order->getUser() !== $user) {
            throw $this->createNotFoundException('Order not found or you do not have permission to view it');
        }

        $orderAddress = $order->getOrderAddress();  

        return $this->render('customer_order/order_details.html.twig', [
            'order' => $order,
            'orderAddress' => $orderAddress  
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/orders', name: 'admin_orders')]
    public function showAllOrders(CustomerOrderRepository $orderRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $orders = $orderRepo->findAllOrdersByNewest();

        return $this->render('customer_order/admin_orders.html.twig', [
            'orders' => $orders
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/order/{orderId}/details', name: 'admin_order_details')]
    public function adminOrderDetails($orderId, CustomerOrderRepository $orderRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $order = $orderRepository->find($orderId);

        if (!$order) {
            throw $this->createNotFoundException('Order not found');
        }

        $orderAddress = $order->getOrderAddress();

        return $this->render('customer_order/admin_order_details.html.twig', [
            'order' => $order,
            'orderAddress' => $orderAddress
        ]);
    }


    #[Route('/profile', name: 'user_profile', methods: ['GET', 'POST'])]
public function profile(Request $request, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser(); // Récupère l'utilisateur actuellement connecté

    if (!$user) {
        return $this->redirectToRoute('app_login'); // Redirige si non connecté
    }

    // Créer le formulaire pour modifier le profil de l'utilisateur
    $form = $this->createForm(UserProfileType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Met à jour l'utilisateur et sauvegarde les modifications
        $entityManager->flush();

        $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

        return $this->redirectToRoute('user_profile'); // Redirige après la mise à jour
    }

    return $this->render('user/profile.html.twig', [
        'form' => $form->createView(),
    ]);
}

}
