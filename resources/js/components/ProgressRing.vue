<template>
    <div class="relative inline-flex items-center justify-center">
      <svg
        :width="size"
        :height="size"
        class="transform -rotate-90"
        :class="class"
      >
        <!-- Background circle -->
        <circle
          :cx="center"
          :cy="center"
          :r="radius"
          :stroke-width="strokeWidth"
          stroke="currentColor"
          fill="none"
          class="text-gray-200 opacity-20"
        />
        
        <!-- Progress circle -->
        <circle
          :cx="center"
          :cy="center"
          :r="radius"
          :stroke-width="strokeWidth"
          stroke="currentColor"
          fill="none"
          :stroke-dasharray="circumference"
          :stroke-dashoffset="strokeDashoffset"
          :stroke-linecap="rounded ? 'round' : 'butt'"
          class="transition-all duration-1000 ease-out"
          :class="progressColor"
        />
      </svg>
      
      <!-- Center content -->
      <div class="absolute inset-0 flex items-center justify-center">
        <div class="text-center text-gray-800">
          <div :class="textSizeClass" class="font-bold leading-none">
            {{ Math.round(progress) }}%
          </div>
          <div v-if="subtitle" :class="subtitleSizeClass" class="text-gray-500 mt-1">
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
  
  const progressColor = computed(() => {
    const colorMap = {
      blue: 'text-blue-500',
      green: 'text-green-500',
      purple: 'text-purple-500',
      red: 'text-red-500',
      yellow: 'text-yellow-500',
      indigo: 'text-indigo-500'
    }
    return colorMap[props.color] || 'text-blue-500'
  })
  
  const textSizeClass = computed(() => {
    if (props.size <= 60) return 'text-xs'
    if (props.size <= 80) return 'text-sm'
    if (props.size <= 120) return 'text-lg'
    return 'text-xl'
  })
  
  const subtitleSizeClass = computed(() => {
    if (props.size <= 60) return 'text-xs'
    if (props.size <= 80) return 'text-xs'
    if (props.size <= 120) return 'text-sm'
    return 'text-base'
  })
  </script>