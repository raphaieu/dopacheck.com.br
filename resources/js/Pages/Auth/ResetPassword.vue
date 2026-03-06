<script setup>
import { useForm } from '@inertiajs/vue3'
import { inject } from 'vue'
import InputError from '@/components/InputError.vue'
import AuthenticationCardLogo from '@/components/LogoRedirect.vue'
import Button from '@/components/ui/button/Button.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import Input from '@/components/ui/input/Input.vue'
import Label from '@/components/ui/label/Label.vue'
import { useSeoMetaTags } from '@/composables/useSeoMetaTags.js'

const props = defineProps({
  email: String,
  token: String,
})

useSeoMetaTags({
  title: 'Redefinir Senha',
})

const route = inject('route')
const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
})

function submit() {
  form.post(route('password.update'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <div class="relative flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-blue-50 via-white to-purple-50 overflow-hidden">
    <!-- Decorative Blurs -->
    <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-blue-400/10 rounded-full blur-[120px] animate-pulse pointer-events-none"></div>
    <div class="absolute top-[20%] -right-[10%] w-[35%] h-[35%] bg-purple-400/10 rounded-full blur-[120px] animate-pulse pointer-events-none" style="animation-delay: 2s"></div>
    <div class="absolute -bottom-[10%] left-[20%] w-[30%] h-[30%] bg-emerald-400/10 rounded-full blur-[120px] animate-pulse pointer-events-none" style="animation-delay: 4s"></div>

    <Card class="relative mx-auto w-[95%] max-w-[440px] shadow-2xl shadow-slate-200/50 border border-white/80 bg-white/70 backdrop-blur-xl rounded-[2.5rem] p-4 transition-all duration-500 hover:shadow-blue-500/5 overflow-hidden z-10">
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
          Nova senha
        </CardDescription>
      </CardHeader>

      <CardContent class="px-6 pb-8">
        <form @submit.prevent="submit">
          <div class="grid gap-6">
            <div class="grid gap-2.5">
              <Label for="email" class="text-slate-900 font-black uppercase tracking-widest text-[10px] ml-1">E-mail</Label>
              <Input
                id="email" 
                v-model="form.email" 
                type="email" 
                placeholder="seu@email.com"
                class="h-12 border-slate-200 bg-white/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 rounded-xl transition-all font-medium"
                required 
                autofocus
                autocomplete="username"
              />
              <InputError :message="form.errors.email" />
            </div>

            <div class="grid gap-2.5">
              <Label for="password" class="text-slate-900 font-black uppercase tracking-widest text-[10px] ml-1">Nova Senha</Label>
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

            <Button 
              type="submit"
              class="h-14 w-full bg-slate-900 hover:bg-slate-800 text-white font-black uppercase tracking-[0.2em] text-sm rounded-2xl shadow-xl shadow-slate-900/10 transition-all active:scale-[0.98] disabled:opacity-50 mt-2"
              :disabled="form.processing"
            >
              {{ form.processing ? 'Redefinindo...' : 'Redefinir Senha' }}
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>

    <!-- Footer Credit -->
    <p class="mt-12 text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] select-none">
       DOPA Check · Premium Edition
    </p>
  </div>
</template>
