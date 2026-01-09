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
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <DopaHeader subtitle="Assinaturas" max-width="4xl" home-link="/dopa" :show-back-button="true" back-link="/dopa" />

    <main class="max-w-4xl mx-auto px-4 py-8 space-y-6">
      <SubscriptionManager
        :active-subscriptions="activeSubscriptions"
        :available-subscriptions="availableSubscriptions"
        :is-pro="!!user?.is_pro"
      />

      <Deferred data="activeInvoices">
        <template #fallback>
          <div class="flex items-center gap-2 text-gray-600">
            <Icon icon="lucide:loader" class="animate-spin" />
            <span class="text-sm">Carregando faturas…</span>
          </div>
        </template>
        <div v-if="activeInvoices.length > 0">
          <Separator class="my-8" />
          <InvoiceManager :invoices="activeInvoices" />
        </div>
      </Deferred>
    </main>
  </div>
</template>
