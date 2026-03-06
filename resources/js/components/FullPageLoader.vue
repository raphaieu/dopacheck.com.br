<template>
  <Teleport to="body">
    <transition name="fade">
      <div v-if="show" class="fixed inset-0 bg-slate-900/40 backdrop-blur-md flex items-center justify-center z-[200]">
        <div class="bg-white/90 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-white/50 flex flex-col items-center gap-4">
          <div class="relative">
            <div class="size-12 rounded-full border-4 border-slate-100 border-t-blue-600 animate-spin"></div>
            <Icon icon="lucide:loader-2" class="size-6 text-blue-600 absolute inset-0 m-auto animate-pulse" />
          </div>
          <span class="text-slate-900 font-bold tracking-tight">Processando...</span>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script setup>
import { Icon } from '@iconify/vue'

defineProps({
  show: {
    type: Boolean,
    default: false,
  },
})
</script>

