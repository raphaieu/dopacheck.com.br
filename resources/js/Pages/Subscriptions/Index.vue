<script setup>
import { Icon } from '@iconify/vue'
import { Deferred, usePage } from '@inertiajs/vue3'
import Separator from '@/components/ui/separator/Separator.vue'
import DopaHeader from '@/components/DopaHeader.vue'
import { useSeoMetaTags } from '@/composables/useSeoMetaTags.js'
import { computed } from 'vue'

import SubscriptionManager from '@/Pages/Subscriptions/Partials/SubscriptionManager.vue'
import InvoiceManager from './Partials/InvoiceManager.vue'

useSeoMetaTags({
  title: 'Assinaturas',
})

const page = usePage()
const user = computed(() => page.props.auth?.user || null)

defineProps({
  activeSubscriptions: {
    type: Array,
    default: () => [],
  },
  availableSubscriptions: {
    type: Array,
    default: () => [],
  },
  activeInvoices: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <div class="relative min-h-screen overflow-hidden bg-slate-50/50 selection:bg-blue-100 selection:text-blue-900 pt-28">
    <!-- Atmospheric Blurs -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-blue-400/10 blur-[120px] rounded-full animate-pulse"></div>
        <div class="absolute top-[20%] -right-[5%] w-[35%] h-[35%] bg-purple-400/10 blur-[100px] rounded-full animate-pulse" style="animation-delay: -2s"></div>
        <div class="absolute -bottom-[10%] left-[20%] w-[30%] h-[30%] bg-emerald-400/10 blur-[80px] rounded-full animate-pulse" style="animation-delay: -4s"></div>
    </div>

    <DopaHeader subtitle="Assinaturas" max-width="4xl" home-link="/dopa" :show-back-button="true" back-link="/dopa" />

    <main class="relative z-10 max-w-4xl mx-auto px-6 py-12 space-y-12">
      <div class="animate-in fade-in slide-in-from-bottom-4 duration-700">
        <SubscriptionManager
          :active-subscriptions="activeSubscriptions"
          :available-subscriptions="availableSubscriptions"
          :is-pro="!!user?.is_pro"
        />
      </div>

      <Deferred data="activeInvoices">
        <template #fallback>
          <div class="flex flex-col items-center justify-center p-12 space-y-4 bg-white/40 backdrop-blur-xl rounded-[2.5rem] border border-white/80 shadow-xl shadow-slate-200/50">
            <Icon icon="lucide:loader-2" class="size-10 text-blue-600 animate-spin" />
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Sincronizando faturas do Stripe...</span>
          </div>
        </template>
        
        <div v-if="activeInvoices.length > 0" class="animate-in fade-in slide-in-from-bottom-6 duration-700 delay-200">
          <div class="h-[1px] w-full bg-gradient-to-r from-transparent via-slate-200 to-transparent my-16"></div>
          <InvoiceManager :invoices="activeInvoices" />
        </div>
      </Deferred>
    </main>
  </div>
</template>

<style scoped>
@keyframes pulse-soft {
    0%, 100% { transform: scale(1); opacity: 0.1; }
    50% { transform: scale(1.1); opacity: 0.15; }
}

.animate-pulse {
    animation: pulse-soft 8s infinite ease-in-out;
}
</style>
