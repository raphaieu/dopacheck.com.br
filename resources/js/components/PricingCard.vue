<script setup>
import { CheckIcon } from 'lucide-vue-next'
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
  }).format(value)
})
</script>

<template>
  <Card class="w-full bg-white rounded-2xl shadow-sm border border-gray-100" :class="className">
    <div class="p-6 sm:p-7">
      <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
        <div class="space-y-3">
          <slot name="header">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900">
              {{ plan }}
            </h3>
            <p class="text-sm text-gray-600">
              {{ description }}
            </p>
          </slot>

          <slot name="features">
            <ul class="grid gap-2 text-sm text-gray-600 sm:grid-cols-2">
              <li v-for="feature in features" :key="feature" class="flex items-start gap-2">
                <CheckIcon class="mt-0.5 h-4 w-4 text-purple-600" />
                <span>{{ feature }}</span>
              </li>
            </ul>
          </slot>
        </div>

        <div class="flex flex-col items-stretch md:items-end gap-3 md:min-w-[220px]">
          <slot name="pricing">
            <div class="text-left md:text-right">
              <h4 class="text-4xl sm:text-5xl font-black tracking-tight text-gray-900">
                {{ formattedPrice }}
              </h4>
              <p class="text-sm font-medium text-gray-600">
                {{ billingPeriod }}
              </p>
            </div>
          </slot>

          <slot name="action">
            <Button
              as="a"
              :variant="buttonVariant"
              :href="buttonHref"
              class="w-full md:w-[220px] bg-gradient-to-r from-blue-600 to-purple-600 text-white hover:from-blue-700 hover:to-purple-700"
            >
              {{ buttonText }}
            </Button>
          </slot>
        </div>
      </div>
    </div>

    <CardFooter v-if="$slots.footer" class="p-4">
      <slot name="footer" />
    </CardFooter>
  </Card>
</template>
