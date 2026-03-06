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
  <div class="relative flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-blue-50 via-white to-purple-50 overflow-hidden py-12">
    <!-- Decorative Blurs -->
    <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-blue-400/10 rounded-full blur-[120px] animate-pulse pointer-events-none"></div>
    <div class="absolute top-[20%] -right-[10%] w-[35%] h-[35%] bg-purple-400/10 rounded-full blur-[120px] animate-pulse pointer-events-none" style="animation-delay: 2s"></div>
    <div class="absolute -bottom-[10%] left-[20%] w-[30%] h-[30%] bg-emerald-400/10 rounded-full blur-[120px] animate-pulse pointer-events-none" style="animation-delay: 4s"></div>

    <Card class="relative mx-auto w-[95%] max-w-[500px] shadow-2xl shadow-slate-200/50 border border-white/80 bg-white/70 backdrop-blur-xl rounded-[2.5rem] p-4 transition-all duration-500 hover:shadow-blue-500/5 overflow-hidden z-10">
      <!-- Header -->
      <CardHeader class="space-y-6 pb-8 pt-6">
        <CardTitle class="flex flex-col items-center gap-4">
          <div class="size-16 rounded-2xl bg-gradient-to-br from-blue-600 via-violet-600 to-purple-600 flex items-center justify-center shadow-xl shadow-blue-600/20 transform hover:rotate-6 transition-transform duration-300">
             <span class="text-white text-3xl select-none">🧠</span>
          </div>
          <div class="text-center">
            <h1 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">DOPA Check</h1>
          </div>
        </CardTitle>
        <CardDescription class="text-center text-lg font-black text-slate-500 uppercase tracking-widest italic">
          Crie sua conta
        </CardDescription>
      </CardHeader>

      <CardContent class="px-6 pb-8">
        <form @submit.prevent="submit">
          <div class="grid gap-6">
            <div class="grid gap-2.5">
              <Label for="name" class="text-slate-900 font-black uppercase tracking-widest text-[10px] ml-1">Nome</Label>
              <Input 
                id="name" 
                v-model="form.name" 
                type="text" 
                placeholder="Seu nome completo"
                class="h-12 border-slate-200 bg-white/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 rounded-xl transition-all font-medium"
                required 
                autofocus 
                autocomplete="name" 
              />
              <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2.5">
              <Label for="email" class="text-slate-900 font-black uppercase tracking-widest text-[10px] ml-1">E-mail</Label>
              <Input 
                id="email" 
                v-model="form.email" 
                type="email" 
                placeholder="seu@email.com"
                class="h-12 border-slate-200 bg-white/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 rounded-xl transition-all font-medium"
                required 
                autocomplete="username" 
              />
              <InputError :message="form.errors.email" />
            </div>

            <div class="grid gap-2.5">
              <Label for="password" class="text-slate-900 font-black uppercase tracking-widest text-[10px] ml-1">Senha</Label>
              <Input
                id="password" 
                v-model="form.password" 
                type="password" 
                placeholder="Mínimo 8 caracteres"
                class="h-12 border-slate-200 bg-white/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 rounded-xl transition-all font-medium"
                required
                autocomplete="new-password"
              />
              <InputError :message="form.errors.password" />
            </div>

            <div class="grid gap-2.5">
              <Label for="password_confirmation" class="text-slate-900 font-black uppercase tracking-widest text-[10px] ml-1">Confirmar Senha</Label>
              <Input
                id="password_confirmation" 
                v-model="form.password_confirmation" 
                type="password" 
                placeholder="Digite a senha novamente"
                class="h-12 border-slate-200 bg-white/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 rounded-xl transition-all font-medium"
                required 
                autocomplete="new-password"
              />
              <InputError :message="form.errors.password_confirmation" />
            </div>

            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature">
              <div class="flex items-start space-x-3 bg-slate-50/50 backdrop-blur-sm border border-slate-100 rounded-2xl p-4">
                <Checkbox id="terms" v-model:checked="form.terms" name="terms" required class="mt-1 rounded-md border-slate-300 text-blue-600 focus:ring-blue-500/20" />
                <label for="terms" class="text-[11px] text-slate-500 font-black uppercase tracking-widest cursor-pointer leading-relaxed">
                  Eu concordo com os
                  <a target="_blank" :href="route('terms.show')" class="text-blue-600 hover:text-blue-700 underline underline-offset-4 decoration-blue-200">Termos</a>
                  e a
                  <a target="_blank" :href="route('policy.show')" class="text-blue-600 hover:text-blue-700 underline underline-offset-4 decoration-blue-200">Privacidade</a>
                </label>
              </div>
              <InputError :message="form.errors.terms" />
            </div>

            <Button 
              type="submit"
              class="cursor-pointer h-14 w-full bg-slate-900 hover:bg-slate-800 text-white font-black uppercase tracking-[0.2em] text-sm rounded-2xl shadow-xl shadow-slate-900/10 transition-all active:scale-[0.98] disabled:opacity-50 mt-2" 
              :disabled="form.processing"
            >
              {{ form.processing ? 'Criando conta...' : 'Criar Conta' }}
            </Button>

            <div class="text-center text-[11px] font-black text-slate-500 uppercase tracking-widest mt-4">
              Já tem uma conta?
              <Link :href="route('login')" class="text-blue-600 hover:text-blue-700 underline decoration-blue-200 underline-offset-4 decoration-2 transition-all">
                Entrar
              </Link>
            </div>
          </div>
        </form>

        <!-- OAuth Section -->
        <div v-if="hasOauthProviders" class="mt-10">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <span class="w-full border-t border-slate-200" />
            </div>
            <div class="relative flex justify-center text-[10px] font-black uppercase tracking-[0.2em]">
              <span class="bg-white/70 backdrop-blur-xl px-4 text-slate-400">
                Ou continue com
              </span>
            </div>
          </div>

          <div class="mt-8 grid gap-3">
            <SocialLoginButton
              v-for="provider in availableOauthProviders"
              :key="provider.slug"
              :provider="provider"
              :disabled="form.processing"
              class="h-12 rounded-xl"
            />
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Footer Credit -->
    <p class="mt-12 text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] select-none">
       DOPA Check · Built for consistency.
    </p>
  </div>
</template>
