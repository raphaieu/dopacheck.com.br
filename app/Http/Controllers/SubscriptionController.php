<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Laravel\Cashier\Checkout;
use Laravel\Cashier\Subscription;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Stripe\Exception\InvalidRequestException;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\Subscription as StripeSubscription;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class SubscriptionController extends Controller
{
    /**
     * @return array<int, string>
     */
    private function proPriceIds(): array
    {
        /** @var array<int, array<string, mixed>> $configuredPlans */
        $configuredPlans = (array) config('subscriptions.subscriptions', []);

        return collect($configuredPlans)
            ->pluck('price_id')
            ->filter(fn ($id) => is_string($id) && $id !== '')
            ->values()
            ->all();
    }

    private function syncDopaProFromStripeSubscription(User $user, StripeSubscription $subscription): void
    {
        $proPriceIds = $this->proPriceIds();

        $hasProItem = false;
        foreach ($subscription->items->data ?? [] as $item) {
            $priceId = $item->price->id ?? null;
            if (is_string($priceId) && in_array($priceId, $proPriceIds, true)) {
                $hasProItem = true;
                break;
            }
        }

        $isActive = $hasProItem && in_array($subscription->status, ['active', 'trialing'], true);

        if ($isActive) {
            $user->forceFill([
                'plan' => 'pro',
                'subscription_ends_at' => $subscription->current_period_end
                    ? now()->createFromTimestamp((int) $subscription->current_period_end)
                    : null,
            ])->save();

            return;
        }

        $user->forceFill([
            'plan' => 'free',
            'subscription_ends_at' => null,
        ])->save();
    }

    /**
     * Redirect authenticated user to Stripe billing portal.
     */
    public function index(): RedirectResponse
    {
        if (! Config::get('cashier.billing_enabled')) {
            return redirect()->route('dopa.dashboard');
        }

        /** @var User $user */
        $user = type(Auth::user())->as(User::class);

        // Se ainda não tem assinatura ativa, manda para a página de planos/checkout.
        if (! $user->subscribed()) {
            return redirect()->route('subscriptions.create');
        }

        // Caso já seja assinante, manda direto para o Billing Portal (gerência/2ª via/cancelamento).
        return $user->redirectToBillingPortal(route('subscriptions.create'));
    }

    /**
     * Display subscription management page with active subscriptions and available plans.
     */
    public function create(Request $request): Response|RedirectResponse
    {
        if (! Config::get('cashier.billing_enabled')) {
            return redirect()->route('dopa.dashboard');
        }

        /** @var User $user */
        $user = $request->user();

        // Se o usuário voltou do Stripe Checkout, tentamos sincronizar o estado PRO imediatamente,
        // mesmo que o webhook ainda não tenha sido configurado no ambiente local.
        $sessionId = $request->query('session_id');
        if (is_string($sessionId) && $sessionId !== '') {
            try {
                Stripe::setApiKey((string) config('cashier.secret'));

                $session = StripeCheckoutSession::retrieve($sessionId, [
                    'expand' => ['subscription'],
                ]);

                // Atualiza stripe_id local (caso ainda não esteja preenchido).
                if (is_string($session->customer ?? null) && ! $user->hasStripeId()) {
                    $user->forceFill(['stripe_id' => $session->customer])->save();
                }

                $subscriptionId = is_string($session->subscription ?? null) ? $session->subscription : null;
                if ($subscriptionId && $subscriptionId !== '') {
                    $subscription = StripeSubscription::retrieve($subscriptionId, [
                        'expand' => ['items.data.price'],
                    ]);
                    $this->syncDopaProFromStripeSubscription($user, $subscription);
                }
            } catch (\Throwable) {
                // Silencioso: a tela de planos não deve quebrar por causa de sync.
            }
        } elseif ($request->query('checkout') === 'success' && $user->hasStripeId()) {
            // Fallback para fluxos antigos (onde ainda não passávamos session_id no success_url).
            // Tenta encontrar a assinatura mais recente no Stripe e sincroniza.
            try {
                Stripe::setApiKey((string) config('cashier.secret'));

                $subs = StripeSubscription::all([
                    'customer' => $user->stripe_id,
                    'status' => 'all',
                    'limit' => 10,
                    'expand' => ['data.items.data.price'],
                ]);

                /** @var StripeSubscription|null $best */
                $best = null;
                foreach ($subs->data ?? [] as $sub) {
                    if (! $sub instanceof StripeSubscription) {
                        continue;
                    }
                    // Escolhe a mais “recente” por current_period_end (quando disponível).
                    if (! $best || ((int) ($sub->current_period_end ?? 0)) > ((int) ($best->current_period_end ?? 0))) {
                        $best = $sub;
                    }
                }

                if ($best) {
                    $this->syncDopaProFromStripeSubscription($user, $best);
                }
            } catch (\Throwable) {
                // Silencioso por UX: não derruba a tela.
            }
        } elseif (! $user->is_pro && $user->hasStripeId()) {
            // Fallback geral para casos em que:
            // - o webhook não está configurado ainda; e
            // - o usuário já tem stripe_id; e
            // - o bridge do produto ainda não foi atualizado.
            //
            // Faz uma consulta rápida no Stripe e sincroniza o estado PRO.
            try {
                Stripe::setApiKey((string) config('cashier.secret'));

                $subs = StripeSubscription::all([
                    'customer' => $user->stripe_id,
                    'status' => 'all',
                    'limit' => 10,
                    'expand' => ['data.items.data.price'],
                ]);

                /** @var StripeSubscription|null $bestActive */
                $bestActive = null;
                /** @var StripeSubscription|null $bestAny */
                $bestAny = null;

                foreach ($subs->data ?? [] as $sub) {
                    if (! $sub instanceof StripeSubscription) {
                        continue;
                    }

                    if (! $bestAny || ((int) ($sub->current_period_end ?? 0)) > ((int) ($bestAny->current_period_end ?? 0))) {
                        $bestAny = $sub;
                    }

                    if (in_array($sub->status, ['active', 'trialing'], true)) {
                        if (! $bestActive || ((int) ($sub->current_period_end ?? 0)) > ((int) ($bestActive->current_period_end ?? 0))) {
                            $bestActive = $sub;
                        }
                    }
                }

                if ($bestActive) {
                    $this->syncDopaProFromStripeSubscription($user, $bestActive);
                } elseif ($bestAny) {
                    $this->syncDopaProFromStripeSubscription($user, $bestAny);
                }
            } catch (\Throwable) {
                // Silencioso por UX.
            }
        }

        /** @var Collection<int, Subscription> $activeSubscriptions */
        $activeSubscriptions = Subscription::query()->where(['user_id' => $user->id])->active()->get();

        /** @var array<int, array<string, mixed>> $availableSubscriptions */
        $availableSubscriptions = collect(config('subscriptions.subscriptions', []))
            ->filter(fn ($plan) => filled(Arr::get($plan, 'price_id')))
            ->values()
            ->all();

        return Inertia::render('Subscriptions/Index', [
            'activeSubscriptions' => $activeSubscriptions,
            'availableSubscriptions' => $availableSubscriptions,
            'activeInvoices' => Inertia::defer(function () use ($user) {
                // Evita quebrar a tela em ambientes onde existe stripe_id "antigo"
                // (ex.: trocou de chaves test/live, resetou Stripe etc).
                if (! $user->hasStripeId()) {
                    return [];
                }

                try {
                    return $user->invoices();
                } catch (InvalidRequestException $e) {
                    // Caso comum em dev: customer não existe na conta (test vs live).
                    if (str_contains($e->getMessage(), 'No such customer')) {
                        $user->forceFill([
                            'stripe_id' => null,
                            'pm_type' => null,
                            'pm_last_four' => null,
                        ])->save();
                    }

                    return [];
                }
            }),
        ]);
    }

    /**
     * Create Stripe Checkout session for selected subscription plan.
     *
     * @throws NotFoundHttpException If subscription plan not found
     */
    public function show(string $subscription): Checkout|RedirectResponse
    {
        if (! Config::get('cashier.billing_enabled')) {
            return redirect()->route('dopa.dashboard');
        }

        /** @var array<int, array<string, mixed>> $subscriptionConfig */
        $subscriptionConfig = Config::array('subscriptions.subscriptions');
        $subscriptions = collect($subscriptionConfig);

        abort_unless(
            in_array($subscription, $subscriptions->pluck('price_id')->toArray()),
            404
        );

        /** @var User $user */
        $user = request()->user();

        /** @var array<string, mixed>|null $subscriptionData */
        $subscriptionData = $subscriptions->firstWhere('price_id', $subscription);

        abort_if($subscriptionData === null, 404);

        $name = type(Arr::get($subscriptionData, 'plan'))->asString();

        // Não use route() com {CHECKOUT_SESSION_ID} no array porque ele faz URL-encode das chaves.
        $successUrl = route('subscriptions.create', ['checkout' => 'success']).'&session_id={CHECKOUT_SESSION_ID}';

        return $user
            ->newSubscription($name, $subscription)
            ->checkout([
                'success_url' => $successUrl,
                'cancel_url' => route('subscriptions.create'),
            ]);
    }
}
