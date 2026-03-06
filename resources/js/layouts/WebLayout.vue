<script setup>
import DopaHeaderGuest from '@/components/DopaHeaderGuest.vue'

defineProps({
  canLogin: {
    type: Boolean,
  },
  canRegister: {
    type: Boolean,
  },
})
</script>

<template>
  <div class="min-h-screen pt-16 overflow-x-clip">
    <DopaHeaderGuest home-link="/" />

    <slot />

  </div>
</template>
