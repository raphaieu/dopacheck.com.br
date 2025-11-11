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
  <div class="min-h-screen">
    <DopaHeaderGuest home-link="/" />

    <slot />

    <!-- Footer -->
    <footer class="border-t">
      <div class="container mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
          <p class="text-sm text-center sm:text-left text-gray-600">
            © {{ new Date().getFullYear() }} DOPA Check. Todos os direitos reservados.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>
