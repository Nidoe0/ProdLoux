<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Seller;
use RuntimeException;

class StripeService
{
    public function __construct()
    {
        if (! class_exists(\Stripe\Stripe::class)) {
            throw new RuntimeException('Le package stripe/stripe-php n\'est pas installé. Lancez : composer require stripe/stripe-php');
        }
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe PaymentIntent for an order.
     * Amount is in Ariary — we use EUR as proxy (1 EUR ≈ 5000 Ar).
     * For production, use a real MGA→EUR conversion or a local payment gateway.
     */
    public function createPaymentIntent(Order $order): array
    {
        // Convert Ariary to centimes EUR (approximate — adjust rate as needed)
        $amountCents = (int) round($order->total / 50); // rough 1 EUR = 5000 Ar

        $intent = \Stripe\PaymentIntent::create([
            'amount'               => max($amountCents, 50), // Stripe minimum is 50 cents
            'currency'             => 'eur',
            'metadata'             => [
                'order_id' => $order->id,
                'user_id'  => $order->user_id,
            ],
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        return [
            'client_secret'     => $intent->client_secret,
            'payment_intent_id' => $intent->id,
        ];
    }

    /**
     * Create a Stripe Connect Express account for a seller.
     */
    public function createConnectAccount(Seller $seller): string
    {
        $account = \Stripe\Account::create([
            'type'         => 'express',
            'country'      => 'FR', // MG not supported yet; use FR as fallback
            'email'        => $seller->user->email,
            'capabilities' => ['transfers' => ['requested' => true]],
            'metadata'     => ['seller_id' => $seller->id],
        ]);

        $seller->update([
            'stripe_account_id' => $account->id,
            'stripe_onboarded'  => false,
        ]);

        return $account->id;
    }

    /**
     * Generate an onboarding link for a seller's Connect account.
     */
    public function getOnboardingLink(Seller $seller, string $returnUrl): string
    {
        $link = \Stripe\AccountLink::create([
            'account'     => $seller->stripe_account_id,
            'refresh_url' => $returnUrl,
            'return_url'  => $returnUrl,
            'type'        => 'account_onboarding',
        ]);

        return $link->url;
    }

    /**
     * Transfer seller's share to their Connect account.
     */
    public function transferToSeller(string $stripeAccountId, int $amountCents, string $paymentIntentId): ?\Stripe\Transfer
    {
        if (! $stripeAccountId || $amountCents <= 0) {
            return null;
        }

        return \Stripe\Transfer::create([
            'amount'             => $amountCents,
            'currency'           => 'eur',
            'destination'        => $stripeAccountId,
            'source_transaction' => $paymentIntentId,
        ]);
    }

    /**
     * Retrieve a PaymentIntent by ID.
     */
    public function retrieveIntent(string $intentId): \Stripe\PaymentIntent
    {
        return \Stripe\PaymentIntent::retrieve($intentId);
    }
}
