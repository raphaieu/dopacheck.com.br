<template>
  <div class="relative inline-flex items-center justify-center group/ring">
    <svg
      :width="size"
      :height="size"
      class="transform -rotate-90"
      :class="props.class"
    >
      <defs>
        <linearGradient :id="`gradient-${color}`" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" :style="{ stopColor: gradientColors.start, stopOpacity: 1 }" />
          <stop offset="100%" :style="{ stopColor: gradientColors.end, stopOpacity: 1 }" />
        </linearGradient>
        <filter :id="`glow-${color}`" x="-20%" y="-20%" width="140%" height="140%">
          <feGaussianBlur stdDeviation="2" result="blur" />
          <feComposite in="SourceGraphic" in2="blur" operator="over" />
        </filter>
      </defs>

      <!-- Background circle -->
      <circle
        :cx="center"
        :cy="center"
        :r="radius"
        :stroke-width="strokeWidth"
        stroke="currentColor"
        fill="none"
        class="text-slate-200 opacity-30 group-hover/ring:opacity-40 transition-opacity duration-500"
      />
      
      <!-- Progress circle -->
      <circle
        :cx="center"
        :cy="center"
        :r="radius"
        :stroke-width="strokeWidth"
        :stroke="`url(#gradient-${color})`"
        fill="none"
        :stroke-dasharray="circumference"
        :stroke-dashoffset="strokeDashoffset"
        :stroke-linecap="rounded ? 'round' : 'butt'"
        class="transition-all duration-1000 ease-out"
        :filter="`url(#glow-${color})`"
      />
    </svg>
    
    <!-- Center content -->
    <div class="absolute inset-0 flex items-center justify-center">
      <div class="text-center">
        <div :class="[textSizeClass, 'font-black leading-none tracking-tight text-slate-900 drop-shadow-sm']">
          {{ Math.round(progress) }}<span class="text-[0.6em] opacity-40 ml-0.5">%</span>
        </div>
        <div v-if="subtitle" :class="[subtitleSizeClass, 'text-slate-400 font-bold uppercase tracking-widest mt-0.5 scale-90']">
          {{ subtitle }}
        </div>
      </div>
    </div>
  </div>
</template>
  
  <script setup>
  import { computed } from 'vue'
  
  // Props
  const props = defineProps({
    progress: {
      type: Number,
      default: 0,
      validator: (value) => value >= 0 && value <= 100
    },
    size: {
      type: Number,
      default: 120
    },
    strokeWidth: {
      type: Number,
      default: 8
    },
    rounded: {
      type: Boolean,
      default: true
    },
    class: {
      type: String,
      default: ''
    },
    subtitle: {
      type: String,
      default: ''
    },
    color: {
      type: String,
      default: 'blue',
      validator: (value) => ['blue', 'green', 'purple', 'red', 'yellow', 'indigo'].includes(value)
    }
  })
  
  // Computed
  const center = computed(() => props.size / 2)
  const radius = computed(() => (props.size - props.strokeWidth) / 2)
  const circumference = computed(() => 2 * Math.PI * radius.value)
  
  const strokeDashoffset = computed(() => {
    const progress = Math.max(0, Math.min(100, props.progress))
    return circumference.value - (progress / 100) * circumference.value
  })

  const gradientColors = computed(() => {
    const map = {
      blue: { start: '#2563eb', end: '#7c3aed' },
      green: { start: '#059669', end: '#10b981' },
      purple: { start: '#7c3aed', end: '#d946ef' },
      red: { start: '#dc2626', end: '#f87171' },
      yellow: { start: '#ca8a04', end: '#facc15' },
      indigo: { start: '#4f46e5', end: '#818cf8' }
    }
    return map[props.color] || map.blue
  })
  
  const textSizeClass = computed(() => {
    if (props.size <= 60) return 'text-xs'
    if (props.size <= 80) return 'text-sm'
    if (props.size <= 100) return 'text-base'
    if (props.size <= 120) return 'text-lg'
    return 'text-xl'
  })
  
  const subtitleSizeClass = computed(() => {
    if (props.size <= 60) return 'text-[8px]'
    if (props.size <= 80) return 'text-[9px]'
    if (props.size <= 120) return 'text-[10px]'
    return 'text-xs'
  })
</script>