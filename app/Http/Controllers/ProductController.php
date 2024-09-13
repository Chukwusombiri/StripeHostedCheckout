<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::all();
        return view('products.index', compact('products'));
    }

    public function checkout(Request $request)
    {
        $apikey = config('app.stripe_key');
        $products = Product::all();
        $lineItems = [];
        $totalPrice = 0;
        foreach ($products as $product) {
            $totalPrice += $product->price;
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $product->name,
                    ],
                    'unit_amount' => $product->price * 100,
                ],
                'quantity' => 1,
            ];
        }
        $stripe = new \Stripe\StripeClient($apikey);
        $checkout_session = $stripe->checkout->sessions->create([
            'customer_email' => 'customer@example.com',
            'customer_creation' => 'always',
            'line_items' => $lineItems, 
            'mode' => 'payment',
            'success_url' => route('checkout.success', [], true) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel', [], true),
        ]);

        $order = new Order();
        $order->status = 'unpaid';
        $order->total_price = $totalPrice;
        $order->session_id = $checkout_session->id;
        $order->save();

        return redirect($checkout_session->url, 303);
    }

    public function success(Request $request)
    {
        $sessonId = $request->get('session_id');

        try {
            $stripe = new \Stripe\StripeClient(config('app.stripe_key'));

            $session = $stripe->checkout->sessions->retrieve($sessonId);
            if (!$session) {
                throw new NotFoundHttpException;
            }
            if ($session->payment_status != 'unpaid') {
                $order = Order::where('session_id', $session->id)->first();
                if (!$order) {
                    throw new NotFoundHttpException;
                }

                if($order && $order->status=='unpaid'){
                    $order->status = 'paid';
                    $order->save();
                }
            }

            return view('products.checkout-success');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function cancel()
    {
        return view('products.checkout-cancel');
    }

    public function webhook()
    {
        $stripe = new \Stripe\StripeClient(config('app.stripe_key'));
        $endpoint_secret = config('app.stripe_webhook_key');

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response('',400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response('',400);
        }

        // Handle the event
        if (
            $event->type == 'checkout.session.completed'
            || $event->type == 'checkout.session.async_payment_succeeded'
          ) {
                $session = $event->data->object;
                $sessionId = $session->id;
                $order = Order::where('session_id', $sessionId)->first();
                if ($order && $order->status=='unpaid') {                    
                    $order->status = 'paid';
                    $order->save();
                    /* SEND USER EMAILS NOW */
                }
          }elseif($event->type="checkout.session.async_payment_failed"){
            
          }
        
        return response('',200);
    }
}
