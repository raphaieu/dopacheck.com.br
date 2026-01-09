<script setup>
import { Icon } from '@iconify/vue'
import { inject } from 'vue'
import PricingCard from '@/components/PricingCard.vue'
import Alert from '@/components/ui/alert/Alert.vue'
import AlertTitle from '@/components/ui/alert/AlertTitle.vue'
import Button from '@/components/ui/button/Button.vue'

defineProps({
  activeSubscriptions: {
    type: Array,
    default: () => [],
  },
  availableSubscriptions: {
    type: Array,
    default: () => [],
  },
  isPro: {
    type: Boolean,
    default: false,
  },
})

const route = inject('route')
</script>

<template>
  <div class="mt-5 md:col-span-2 md:mt-0">
    <header class="pb-4">
      <h3 class="text-lg font-medium">
        Gerenciar assinatura
      </h3>
    </header>

    <!-- Se já for PRO (bridge do produto), não mostra oferta novamente -->
    <div v-if="isPro" class="flex flex-col space-y-4">
      <Alert>
        <AlertTitle class="flex items-center justify-between">
          <span>✨ Você já é PRO.</span>
          <Button as="a" :href="route('subscriptions.index')">
            Gerenciar no Stripe
          </Button>
        </AlertTitle>
      </Alert>
    </div>

    <div v-else-if="activeSubscriptions.length === 0" class="flex flex-col space-y-4">
      <Alert class="text-md">
        <Icon icon="lucide:triangle-alert" class="size-4" />
        <AlertTitle>
          Você ainda não tem uma assinatura. Assine o PRO para liberar os recursos.
        </AlertTitle>
      </Alert>

      <PricingCard
        v-for="subscription in availableSubscriptions" :key="subscription.price_id"
        :plan="subscription.plan" :price="subscription.price" :description="subscription.description"
        :features="subscription.features"
        :billing-period="subscription.billing_period"
        button-text="Assinar"
        :button-href="route('subscriptions.show', { subscription: subscription.price_id })"
      />
    </div>

    <div v-else>
      <Alert>
        <AlertTitle class="flex items-center justify-between">
          Você está no plano {{ activeSubscriptions[0].type }}.
          <Button as="a" :href="route('subscriptions.index')">
            Gerenciar no Stripe
          </Button>
        </AlertTitle>
      </Alert>
    </div>

    <footer class="mt-2">
      <p class="text-sm text-muted-foreground">
        🔒 As assinaturas são gerenciadas com segurança pela Stripe.
      </p>
    </footer>
  </div>
</template>
