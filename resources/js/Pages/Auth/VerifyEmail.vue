<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { computed, inject } from 'vue'
import AuthenticationCardLogo from '@/components/LogoRedirect.vue'
import Button from '@/components/ui/button/Button.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { useSeoMetaTags } from '@/composables/useSeoMetaTags.js'

const props = defineProps({
  status: String,
})

useSeoMetaTags({
  title: 'Verificar Email',
})

const route = inject('route')

const form = useForm({})

function submit() {
  form.post(route('verification.send'))
}

const verificationLinkSent = computed(() => props.status === 'verification-link-sent')
</script>

<template>
  <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <Card class="mx-auto w-full max-w-lg shadow-xl border-0 transition-all duration-300 hover:shadow-2xl">
      <CardHeader class="space-y-4 pb-6">
        <CardTitle class="flex flex-col items-center gap-3">
          <AuthenticationCardLogo />
          <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-900">DOPA Check</h1>
          </div>
        </CardTitle>
        <CardDescription class="text-center text-lg font-medium text-gray-600">
          Verificar seu email
        </CardDescription>
      </CardHeader>

      <CardContent>
        <div class="mb-6 text-sm text-gray-600 leading-relaxed">
          Antes de continuar, verifique seu endereço de email clicando no link que enviamos para você. 
          Se você não recebeu o email, podemos enviar outro.
        </div>

        <div v-if="verificationLinkSent" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
          <p class="text-sm font-medium text-green-800">
            ✓ Um novo link de verificação foi enviado para o endereço de email fornecido.
          </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <Button 
            type="submit"
            class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-medium"
            :class="{ 'opacity-25': form.processing }" 
            :disabled="form.processing"
          >
            {{ form.processing ? 'Enviando...' : 'Reenviar Email de Verificação' }}
          </Button>

          <div class="flex items-center justify-center gap-4 pt-4 border-t border-gray-200">
            <Link
              :href="route('profile.show')"
              class="text-sm text-blue-600 hover:text-blue-700 hover:underline underline-offset-4 font-medium"
            >
              Editar Perfil
            </Link>

            <span class="text-gray-300">|</span>

            <Link
              :href="route('logout')" 
              method="post" 
              as="button"
              class="text-sm text-blue-600 hover:text-blue-700 hover:underline underline-offset-4 font-medium"
            >
              Sair
            </Link>
          </div>
        </form>
      </CardContent>
    </Card>
  </div>
</template>
