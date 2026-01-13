<script setup>
import { computed } from 'vue'
import { Icon } from '@iconify/vue'
import { useChangeCase } from '@vueuse/integrations/useChangeCase'
import { Button } from '@/components/ui/button'

const props = defineProps({
  provider: {
    type: Object,
    required: true,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})

const isGoogle = computed(() => props.provider?.slug === 'google')

const buttonClass = computed(() =>
  isGoogle.value
    ? 'w-full justify-center border-[#dadce0] bg-white text-[#3c4043] shadow-sm hover:bg-[#f8f9fa] hover:text-[#202124] active:bg-[#f1f3f4] dark:bg-zinc-950 dark:text-zinc-100 dark:border-zinc-800 dark:hover:bg-zinc-900'
    : 'w-full justify-center border-border bg-background text-foreground shadow-sm hover:bg-accent',
)
</script>

<template>
  <Button
    :disabled="disabled"
    variant="outline"
    :class="buttonClass"
    as="a" :href="route('oauth.redirect', { provider: provider.slug })"
  >
    <Icon :icon="provider.icon" class="mr-2 h-4 w-4" />
    Entrar com {{ useChangeCase(provider.slug, 'sentenceCase') }}
  </Button>
</template>
