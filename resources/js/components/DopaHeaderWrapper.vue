<template>
  <DopaHeader v-if="user" v-bind="$attrs" />
  <DopaHeaderGuest v-else v-bind="$attrs" />
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import DopaHeader from './DopaHeader.vue'
import DopaHeaderGuest from './DopaHeaderGuest.vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)
</script>

