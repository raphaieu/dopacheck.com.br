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
  <div class="space-y-12">
    <header class="text-center space-y-2">
      <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tighter italic">Plano & Assinatura</h2>
      <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Gerencie sua experiência DOPA Check PRO</p>
    </header>

    <!-- Se já for PRO -->
    <div v-if="isPro" class="relative group">
      <div class="absolute -inset-[1px] bg-gradient-to-r from-emerald-500/20 via-blue-500/20 to-purple-500/20 rounded-[2.5rem] blur-sm opacity-50 transition-opacity group-hover:opacity-100"></div>
      <div class="relative bg-white/90 backdrop-blur-xl rounded-[2.5rem] p-8 border border-white/80 shadow-2xl shadow-slate-200/50">
        <div class="flex flex-col sm:flex-row items-center gap-8">
          <div class="size-20 rounded-3xl bg-slate-900 flex items-center justify-center text-3xl shadow-2xl rotate-3 group-hover:rotate-0 transition-transform duration-500">
            <Icon icon="lucide:sparkles" class="text-emerald-400 size-10 animate-pulse" />
          </div>
          <div class="flex-1 text-center sm:text-left space-y-1">
            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tighter">Você é DOPA PRO</h3>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Acesso total a todos os recursos liberado</p>
          </div>
          <Button as="a" :href="route('subscriptions.index')" 
            class="h-14 px-8 rounded-2xl bg-slate-900 text-white font-black uppercase tracking-widest text-[10px] shadow-xl shadow-slate-900/10 hover:shadow-2xl hover:scale-105 active:scale-95 transition-all cursor-pointer">
            Gerenciar no Stripe
            <Icon icon="lucide:external-link" class="ml-2 size-4" />
          </Button>
        </div>
      </div>
    </div>

    <!-- Se não tiver assinatura -->
    <div v-else-if="activeSubscriptions.length === 0" class="space-y-10">
      <div class="relative overflow-hidden group">
        <div class="absolute inset-0 bg-blue-600/5 border border-blue-600/10 rounded-[2.5rem] blur-sm px-6 py-4"></div>
        <div class="relative bg-blue-50/50 backdrop-blur-xl rounded-[2.5rem] p-6 border border-blue-100/50 flex items-center gap-4">
          <div class="size-10 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-600/20">
            <Icon icon="lucide:info" class="size-5" />
          </div>
          <p class="text-[10px] font-black uppercase tracking-widest text-blue-900">Assine o PRO para desbloquear o seu potencial máximo.</p>
        </div>
      </div>

      <div class="grid gap-8">
        <PricingCard
          v-for="subscription in availableSubscriptions" :key="subscription.price_id"
          :plan="subscription.plan" :price="subscription.price" :description="subscription.description"
          :features="subscription.features"
          :billing-period="subscription.billing_period"
          button-text="Desbloquear PRO"
          :button-href="route('subscriptions.show', { subscription: subscription.price_id })"
        />
      </div>
    </div>

    <!-- Se tiver assinatura mas não detectada como PRO principal (caso raro/fallback) -->
    <div v-else class="relative group">
      <div class="relative bg-white/90 backdrop-blur-xl rounded-[2.5rem] p-8 border border-white/80 shadow-2xl shadow-slate-200/50 flex flex-col sm:flex-row items-center gap-8">
        <div class="size-20 rounded-3xl bg-slate-100 flex items-center justify-center text-3xl">
          <Icon icon="lucide:credit-card" class="text-slate-400 size-10" />
        </div>
        <div class="flex-1 text-center sm:text-left">
          <h3 class="text-xl font-black text-slate-900 uppercase tracking-tighter italic">Plano: {{ activeSubscriptions[0].type }}</h3>
          <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Assinatura ativa detectada</p>
        </div>
        <Button as="a" :href="route('subscriptions.index')"
          class="h-14 px-8 rounded-2xl bg-slate-900 text-white font-black uppercase tracking-widest text-[10px] shadow-xl shadow-slate-900/10 hover:shadow-2xl transition-all cursor-pointer">
          Gerenciar Pagamento
        </Button>
      </div>
    </div>

    <footer class="flex items-center justify-center gap-3 pt-6">
      <Icon icon="lucide:shield-check" class="text-emerald-500 size-4" />
      <p class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">
        Pagamentos processados com segurança via <span class="text-slate-900">Stripe</span>
      </p>
    </footer>
  </div>
</template>
