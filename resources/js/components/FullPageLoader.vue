<template>
  <Teleport to="body">
    <div
      v-if="show"
      class="fixed inset-0 z-9999 bg-black/30 backdrop-blur-sm flex items-center justify-center"
      aria-live="polite"
      aria-busy="true"
    >
      <div class="bg-white rounded-2xl shadow-xl border border-gray-100 px-6 py-5 w-[min(90vw,420px)]">
        <div class="flex items-center gap-4">
          <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center">
            <div class="w-6 h-6 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
          </div>
          <div class="flex-1">
            <div class="text-base font-semibold text-gray-900">Carregando...</div>
            <div class="text-sm text-gray-600">Aguarde...</div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false,
  },
})
</script>

