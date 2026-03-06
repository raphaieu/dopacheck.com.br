<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { computed, inject } from 'vue'
import AuthenticationCardLogo from '@/components/LogoRedirect.vue'
import Button from '@/components/ui/button/Button.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { useSeoMetaTags } from '@/composables/useSeoMetaTags.js'
import { Icon } from '@iconify/vue'

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
          Verificar email
        </CardDescription>
      </CardHeader>

      <CardContent class="px-6 pb-8">
        <div class="mb-8 flex items-start gap-3 bg-blue-50/40 backdrop-blur-sm border border-blue-100/50 rounded-2xl p-4">
          <Icon icon="lucide:mail-check" class="size-4 text-blue-600 mt-0.5" />
          <p class="text-[10px] text-blue-700 font-black uppercase tracking-widest leading-relaxed">
            Antes de continuar, verifique seu endereço de email clicando no link que enviamos para você. 
            Se você não recebeu o email, podemos enviar outro.
          </p>
        </div>

        <div v-if="verificationLinkSent" class="mb-6 flex items-start gap-3 bg-emerald-50/40 backdrop-blur-sm border border-emerald-100/50 rounded-2xl p-4">
          <Icon icon="lucide:check-circle" class="size-4 text-emerald-600 mt-0.5" />
          <p class="text-[10px] text-emerald-700 font-black uppercase tracking-widest leading-relaxed">
            Um novo link de verificação foi enviado para o seu e-mail.
          </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <Button 
            type="submit"
            class="h-14 w-full bg-slate-900 hover:bg-slate-800 text-white font-black uppercase tracking-[0.2em] text-sm rounded-2xl shadow-xl shadow-slate-900/10 transition-all active:scale-[0.98] disabled:opacity-50"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Enviando...' : 'Reenviar Link' }}
          </Button>

          <div class="flex items-center justify-center gap-6 pt-6 border-t border-slate-100">
            <Link
              :href="route('profile.show')"
              class="text-[10px] font-black text-blue-600 hover:text-blue-700 uppercase tracking-widest transition-colors"
            >
              Editar Perfil
            </Link>

            <div class="w-px h-4 bg-slate-200"></div>

            <Link
              :href="route('logout')" 
              method="post" 
              as="button"
              class="text-[10px] font-black text-slate-400 hover:text-slate-600 uppercase tracking-widest transition-colors"
            >
              Sair
            </Link>
          </div>
        </form>
      </CardContent>
    </Card>

    <!-- Footer Credit -->
    <p class="mt-12 text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] select-none">
       DOPA Check · Built for consistency.
    </p>
  </div>
</template>
