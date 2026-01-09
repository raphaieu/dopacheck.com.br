<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { computed, inject } from 'vue'
import InputError from '@/components/InputError.vue'
import AuthenticationCardLogo from '@/components/LogoRedirect.vue'
import SocialLoginButton from '@/components/SocialLoginButton.vue'

import Button from '@/components/ui/button/Button.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import Checkbox from '@/components/ui/checkbox/Checkbox.vue'
import Input from '@/components/ui/input/Input.vue'
import Label from '@/components/ui/label/Label.vue'
import { useSeoMetaTags } from '@/composables/useSeoMetaTags.js'

const props = defineProps({
  availableOauthProviders: Object,
})

useSeoMetaTags({
  title: 'Cadastre-se',
})

const route = inject('route')
const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false,
})

const hasOauthProviders = computed(() =>
  Object.keys(props.availableOauthProviders || {}).length > 0,
)

function submit() {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
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
          Crie sua conta
        </CardDescription>
      </CardHeader>

      <CardContent>
        <form @submit.prevent="submit">
          <div class="grid gap-4">
            <div class="grid gap-2">
              <Label for="name" class="text-gray-700 font-medium">Nome</Label>
              <Input 
                id="name" 
                v-model="form.name" 
                type="text" 
                placeholder="Seu nome completo"
                class="border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                required 
                autofocus 
                autocomplete="name" 
              />
              <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2">
              <Label for="email" class="text-gray-700 font-medium">E-mail</Label>
              <Input 
                id="email" 
                v-model="form.email" 
                type="email" 
                placeholder="seu@email.com"
                class="border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                required 
                autocomplete="username" 
              />
              <InputError :message="form.errors.email" />
            </div>

            <div class="grid gap-2">
              <Label for="password" class="text-gray-700 font-medium">Senha</Label>
              <Input
                id="password" 
                v-model="form.password" 
                type="password" 
                placeholder="Mínimo 8 caracteres"
                class="border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                required
                autocomplete="new-password"
              />
              <InputError :message="form.errors.password" />
            </div>

            <div class="grid gap-2">
              <Label for="password_confirmation" class="text-gray-700 font-medium">Confirmar Senha</Label>
              <Input
                id="password_confirmation" 
                v-model="form.password_confirmation" 
                type="password" 
                placeholder="Digite a senha novamente"
                class="border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                required 
                autocomplete="new-password"
              />
              <InputError :message="form.errors.password_confirmation" />
            </div>

            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature">
              <div class="flex items-start space-x-2 bg-gray-50 border border-gray-200 rounded-lg p-3">
                <Checkbox id="terms" v-model="form.terms" name="terms" required class="mt-1" />
                <label for="terms" class="text-sm text-gray-600 cursor-pointer leading-relaxed">
                  Eu concordo com os
                  <a target="_blank" :href="route('terms.show')" class="text-blue-600 hover:text-blue-700 hover:underline underline-offset-4">Termos de Serviço</a>
                  e a
                  <a target="_blank" :href="route('policy.show')" class="text-blue-600 hover:text-blue-700 hover:underline underline-offset-4">Política de Privacidade</a>
                </label>
              </div>
              <InputError :message="form.errors.terms" />
            </div>

            <Button 
              type="submit"
              class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-medium mt-2"
              :class="{ 'opacity-25': form.processing }" 
              :disabled="form.processing"
            >
              {{ form.processing ? 'Criando conta...' : 'Criar Conta' }}
            </Button>

            <div class="text-center text-sm text-gray-600 mt-4">
              Já tem uma conta?
              <Link :href="route('login')" class="font-medium text-blue-600 hover:text-blue-700 hover:underline underline-offset-4">
                Entrar
              </Link>
            </div>
          </div>
        </form>

        <!-- OAuth Section -->
        <div v-if="hasOauthProviders" class="mt-6">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <span class="w-full border-t border-gray-200" />
            </div>
            <div class="relative flex justify-center text-xs uppercase">
              <span class="bg-white px-2 text-gray-500">
                Ou continue com
              </span>
            </div>
          </div>

          <div class="mt-6 grid gap-2">
            <SocialLoginButton
              v-for="provider in availableOauthProviders"
              :key="provider.slug"
              :provider="provider"
              :disabled="form.processing"
            />
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>
