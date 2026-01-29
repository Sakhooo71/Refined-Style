<?php

namespace App\Service;

use Stripe;
use Exception;
use App\Twig\CartExtension;
use PayPal\Auth\OAuthTokenCredential;

class PaymentService
{
    private $cartExtension;
    private $stripeSecretKey;
    private $stripeSecretKeySecret;
    private $webhook;

    public function __construct(CartExtension $cartExtension, $stripeSecretKey, $stripeSecretKeySecret, $webhook)
    {
        $this->cartExtension = $cartExtension;
        $this->stripeSecretKey = $stripeSecretKey;
        $this->stripeSecretKeySecret = $stripeSecretKeySecret;
        $this->webhook = $webhook;

        Stripe\Stripe::setApiKey($this->stripeSecretKeySecret);
    }

    public function createStripeSession($orderId, $totalPrice)
    {
        // Pas besoin de récupérer le prix total depuis CartExtension ici
        $totalPrice = $this->cartExtension->getTotalPrice();

        error_log("Total Price before Stripe Session: " . $totalPrice);

        $YOUR_DOMAIN = $_ENV['YOUR_DOMAIN'];

        $checkout_session = Stripe\Checkout\Session::create([
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $totalPrice * 100,
                    'product_data' => [
                        'name' => 'Order #' . $orderId,
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/order-confirmation/' . $orderId,
            'cancel_url' => $YOUR_DOMAIN . '/cancel',
            'metadata' => ['order_id' => $orderId],
        ]);

        return $checkout_session;
    }


    public function createPaypalSession($orderId, $totalPrice)
    {
        $clientId = $_ENV["PAYPAL_CLIENT_ID"];
        $clientSecret = $_ENV["PAYPAL_SECRET"];
    
        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                $clientId,
                $clientSecret
            )
        );
    
        $YOUR_DOMAIN = $_ENV['YOUR_DOMAIN'];
    
        $payment = new \PayPal\Api\Payment();
    
        $payment->setIntent('sale')
            ->setPayer(new \PayPal\Api\Payer(['payment_method' => 'paypal']))
            ->setTransactions([new \PayPal\Api\Transaction([
                'amount' => [
                    'total' => $totalPrice,
                    'currency' => 'EUR'
                ],
                'description' => 'Order #' . $orderId
            ])])
            ->setRedirectUrls(new \PayPal\Api\RedirectUrls([
                'return_url' => $YOUR_DOMAIN . '/order-confirmation/' . $orderId,
                'cancel_url' => $YOUR_DOMAIN . '/cancel',
            ]));
    
            try {
                $payment->create($apiContext);
            } catch (Exception $ex) {
                error_log("PayPal Payment Creation Failed: " . $ex->getMessage());
                return null;
            }
            
    
        return $payment;
    }
   
}
