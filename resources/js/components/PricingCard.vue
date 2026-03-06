<template>
  <Card class="relative group overflow-hidden bg-white/90 backdrop-blur-xl rounded-[2.5rem] border border-white/80 shadow-2xl shadow-slate-200/40 transition-all duration-500 hover:shadow-blue-500/10 hover:shadow-3xl hover:-translate-y-1" :class="className">
    <!-- Accent Gradient -->
    <div class="absolute top-0 inset-x-0 h-[2px] bg-gradient-to-r from-blue-600 via-violet-600 to-purple-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>

    <div class="p-8 sm:p-10">
      <div class="flex flex-col gap-8 md:flex-row md:items-center md:justify-between">
        <div class="space-y-6">
          <slot name="header">
            <div class="space-y-2">
              <h3 class="text-3xl font-black text-slate-900 uppercase tracking-tighter italic">
                {{ plan }}
              </h3>
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                {{ description }}
              </p>
            </div>
          </slot>

          <slot name="features">
            <ul class="grid gap-4 text-[10px] font-black uppercase tracking-widest text-slate-500 sm:grid-cols-2">
              <li v-for="feature in features" :key="feature" class="flex items-center gap-3 group/feat">
                <div class="size-5 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 group-hover/feat:bg-blue-600 group-hover/feat:text-white transition-colors">
                  <CheckIcon class="h-3 w-3" />
                </div>
                <span class="group-hover/feat:text-slate-900 transition-colors">{{ feature }}</span>
              </li>
            </ul>
          </slot>
        </div>

        <div class="flex flex-col items-stretch md:items-end gap-6 md:min-w-[240px]">
          <slot name="pricing">
            <div class="text-left md:text-right space-y-1">
              <div class="flex items-baseline md:justify-end gap-1">
                <h4 class="text-5xl sm:text-6xl font-black tracking-tighter text-slate-900 italic">
                  {{ formattedPrice }}
                </h4>
              </div>
              <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                {{ billingPeriod }}
              </p>
            </div>
          </slot>

          <slot name="action">
            <Button
              as="a"
              :variant="buttonVariant"
              :href="buttonHref"
              class="h-16 w-full md:w-[240px] rounded-2xl bg-slate-900 text-white font-black uppercase tracking-widest text-[11px] shadow-xl shadow-slate-900/10 hover:shadow-2xl hover:bg-slate-800 transition-all active:scale-95 cursor-pointer relative overflow-hidden group/btn"
            >
              <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-violet-600 to-purple-600 opacity-0 group-hover/btn:opacity-100 transition-opacity"></div>
              <span class="relative z-10">{{ buttonText }}</span>
              <Icon icon="lucide:arrow-right" class="relative z-10 ml-2 size-4 group-hover/btn:translate-x-1 transition-transform" />
            </Button>
          </slot>
        </div>
      </div>
    </div>

    <CardFooter v-if="$slots.footer" class="px-8 pb-8 pt-0">
      <slot name="footer" />
    </CardFooter>
  </Card>
</template>

<script setup>
import { CheckIcon } from 'lucide-vue-next'
import { Icon } from '@iconify/vue'
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardFooter } from '@/components/ui/card'

const props = defineProps({
  features: {
    type: Array,
    required: false,
    default: () => [
      'Production-ready Docker setup',
      'Advanced authentication system',
      'API endpoints with Sanctum',
      'Comprehensive documentation',
      'Regular updates & improvements',
      'Best In Class IDE support',
    ],
  },
  price: {
    type: Number,
    default: 19,
  },
  plan: {
    type: String,
    default: 'PRO',
  },
  description: {
    type: String,
    default: 'Perfect for growing businesses',
  },
  billingPeriod: {
    type: String,
    default: 'Cobrança mensal',
  },
  buttonText: {
    type: String,
    default: 'Get Started',
  },
  buttonVariant: {
    type: String,
    default: 'default',
  },
  buttonHref: {
    type: String,
    default: '/login',
  },
  className: {
    type: String,
    default: '',
  },
})

const formattedPrice = computed(() => {
  const value = Number(props.price)
  if (Number.isNaN(value)) return String(props.price)

  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value)
})
</script>
