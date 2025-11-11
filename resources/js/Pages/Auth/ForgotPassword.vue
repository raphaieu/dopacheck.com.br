<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { inject } from 'vue'
import InputError from '@/components/InputError.vue'
import AuthenticationCardLogo from '@/components/LogoRedirect.vue'
import Button from '@/components/ui/button/Button.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import Input from '@/components/ui/input/Input.vue'
import Label from '@/components/ui/label/Label.vue'
import { useSeoMetaTags } from '@/composables/useSeoMetaTags.js'

defineProps({
  status: String,
})

useSeoMetaTags({
  title: 'Esqueceu a Senha',
})

const route = inject('route')
const form = useForm({
  email: '',
})

function submit() {
  form.post(route('password.email'))
}
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
          Redefinir senha
        </CardDescription>
      </CardHeader>

      <CardContent>
        <div class="mb-6 text-sm text-gray-600 bg-blue-50 border border-blue-100 rounded-lg p-3">
          🔐 Esqueceu sua senha? Sem problemas. Informe seu e-mail e enviaremos um link para redefinir sua senha.
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600 bg-green-50 border border-green-200 rounded-lg p-3">
          {{ status }}
        </div>

        <form @submit.prevent="submit">
          <div class="grid gap-4">
            <div class="grid gap-2">
              <Label for="email" class="text-gray-700 font-medium">E-mail</Label>
              <Input
                id="email" 
                v-model="form.email" 
                type="email" 
                placeholder="seu@email.com"
                class="border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                required 
                autofocus
                autocomplete="username"
              />
              <InputError :message="form.errors.email" />
            </div>

            <div class="flex items-center justify-between gap-4 mt-2">
              <Link :href="route('login')" class="text-sm text-blue-600 hover:text-blue-700 hover:underline underline-offset-4">
                Voltar para login
              </Link>
              <Button 
                type="submit"
                class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-medium"
                :class="{ 'opacity-25': form.processing }" 
                :disabled="form.processing"
              >
                {{ form.processing ? 'Enviando...' : 'Enviar Link de Redefinição' }}
              </Button>
            </div>
          </div>
        </form>
      </CardContent>
    </Card>
  </div>
</template>
