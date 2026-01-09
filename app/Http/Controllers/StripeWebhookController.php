<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Symfony\Component\HttpFoundation\Response;

final class StripeWebhookController extends CashierWebhookController
{
    /**
     * Sincroniza o estado PRO do DOPA (users.plan + users.subscription_ends_at)
     * a partir do payload do Stripe (customer.subscription.*).
     *
     * Observação:
     * - O Cashier já mantém as tabelas subscriptions/subscription_items via webhook.
     * - Aqui fazemos apenas o "bridge" para os campos legados do produto.
     *
     * @param array<string, mixed> $payload
     */
    private function syncDopaPlanFromSubscriptionPayload(array $payload): void
    {
        $stripeCustomerId = Arr::get($payload, 'data.object.customer');

        if (! is_string($stripeCustomerId) || $stripeCustomerId === '') {
            return;
        }

        /** @var User|null $user */
        $user = User::query()->where('stripe_id', $stripeCustomerId)->first();

        if (! $user) {
            return;
        }

        /** @var array<string, mixed> $subscription */
        $subscription = Arr::get($payload, 'data.object', []);

        /** @var array<int, array<string, mixed>> $configuredPlans */
        $configuredPlans = (array) config('subscriptions.subscriptions', []);

        $proPriceIds = collect($configuredPlans)
            ->pluck('price_id')
            ->filter(fn ($id) => is_string($id) && $id !== '')
            ->values()
            ->all();

        /** @var mixed $items */
        $items = Arr::get($subscription, 'items.data', []);

        $hasProItem = false;
        if (is_array($items)) {
            foreach ($items as $item) {
                $priceId = Arr::get($item, 'price.id');
                if (is_string($priceId) && $priceId !== '' && in_array($priceId, $proPriceIds, true)) {
                    $hasProItem = true;
                    break;
                }
            }
        }

        $status = Arr::get($subscription, 'status');
        $isActive = $hasProItem && in_array($status, ['active', 'trialing'], true);

        $periodEnd = Arr::get($subscription, 'current_period_end');
        $endsAt = null;
        if (is_int($periodEnd) || (is_string($periodEnd) && ctype_digit($periodEnd))) {
            $endsAt = CarbonImmutable::createFromTimestamp((int) $periodEnd);
        }

        if ($isActive) {
            $user->forceFill([
                'plan' => 'pro',
                // O produto usa subscription_ends_at como "até quando é PRO".
                // current_period_end é a data do fim do período vigente.
                'subscription_ends_at' => $endsAt?->toMutable(),
            ])->save();

            return;
        }

        // Sem assinatura PRO ativa -> volta para FREE.
        $user->forceFill([
            'plan' => 'free',
            'subscription_ends_at' => null,
        ])->save();
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function handleCustomerSubscriptionCreated(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionCreated($payload);
        $this->syncDopaPlanFromSubscriptionPayload($payload);

        return $response;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionUpdated($payload);
        $this->syncDopaPlanFromSubscriptionPayload($payload);

        return $response;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionDeleted($payload);
        $this->syncDopaPlanFromSubscriptionPayload($payload);

        return $response;
    }
}

