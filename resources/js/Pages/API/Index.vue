<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import ApiTokenManager from '@/Pages/API/Partials/ApiTokenManager.vue'

defineProps({
  tokens: Array,
  availablePermissions: Array,
  defaultPermissions: Array,
})
</script>

<template>
  <AppLayout title="API Tokens">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight">
        API Tokens
      </h2>
    </template>

    <div>
      <div class="max-w-7xl">
        <ApiTokenManager
          :tokens="tokens"
          :available-permissions="availablePermissions"
          :default-permissions="defaultPermissions"
        />
      </div>
    </div>
  </AppLayout>
</template>
