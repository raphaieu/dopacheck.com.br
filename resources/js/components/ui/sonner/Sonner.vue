<script lang="ts" setup>
import { Toaster as Sonner, type ToasterProps } from 'vue-sonner'

const props = defineProps<ToasterProps>()
</script>

<template>
  <Sonner
    class="toaster group"
    v-bind="props"
    :toast-options="{
      classes: {
        toast: 'group-[.toaster]:rounded-[1.5rem] group-[.toaster]:bg-white/80 group-[.toaster]:backdrop-blur-xl group-[.toaster]:text-slate-900 group-[.toaster]:border-white group-[.toaster]:shadow-2xl group-[.toaster]:shadow-slate-200/50 group-[.toaster]:p-4 group-[.toaster]:flex group-[.toaster]:items-center group-[.toaster]:gap-3',
        title: 'group-[.toast]:text-[10px] group-[.toast]:font-black group-[.toast]:uppercase group-[.toast]:tracking-[0.2em] group-[.toast]:leading-none',
        description: 'group-[.toast]:text-[10px] group-[.toast]:font-bold group-[.toast]:text-slate-500',
        actionButton: 'group-[.toast]:bg-slate-900 group-[.toast]:text-white group-[.toast]:font-black group-[.toast]:uppercase group-[.toast]:tracking-widest group-[.toast]:text-[9px] group-[.toast]:rounded-xl',
        cancelButton: 'group-[.toast]:bg-slate-100 group-[.toast]:text-slate-500',
        success: 'group-[.toaster]:border-emerald-500/20 group-[.toaster]:bg-emerald-50/80',
        error: 'group-[.toaster]:border-red-500/20 group-[.toaster]:bg-red-50/80',
      },
    }"
  />
</template>
