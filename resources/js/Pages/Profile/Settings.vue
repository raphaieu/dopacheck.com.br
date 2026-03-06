<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 pb-20 overflow-x-clip pt-28">
    <!-- Header -->
    <DopaHeader subtitle="Configurações" max-width="4xl" home-link="/dopa" :show-back-button="true" back-link="/dopa" />

    <main class="max-w-4xl mx-auto px-4 py-8 relative">
      <!-- Decorative Blurs -->
      <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl -z-10"></div>
      
      <form @submit.prevent="submit" class="space-y-8">
        <!-- Profile Information -->
        <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-xl shadow-slate-200/50 border border-white/80 p-6 sm:p-8">
          <h2 class="text-xl font-black text-slate-900 mb-8 flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                <Icon icon="lucide:user" class="size-5" />
            </span>
            Informações do Perfil
          </h2>
          
          <div class="grid sm:grid-cols-2 gap-6">
            <div class="sm:col-span-2">
              <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Nome de usuário</label>
              <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                    <Icon icon="lucide:at-sign" class="size-4" />
                </div>
                <input 
                  v-model="form.username" 
                  type="text"
                  class="w-full pl-10 pr-4 py-3 bg-white/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-hidden transition-all placeholder:text-slate-300 font-medium"
                  placeholder="seu_usuario"
                />
              </div>
              <p v-if="form.errors.username" class="text-xs text-red-500 mt-2 font-bold flex items-center gap-1">
                <Icon icon="lucide:alert-circle" class="size-3" /> {{ form.errors.username }}
              </p>
              <p class="text-[10px] text-slate-400 mt-2 font-bold uppercase tracking-wider">Usado na URL do seu perfil público: /u/{{ form.username || 'seu_usuario' }}</p>
            </div>

            <div class="sm:col-span-2">
              <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Apelido (como prefere ser chamado?)</label>
              <input 
                v-model="form.nickname" 
                type="text"
                class="w-full px-4 py-3 bg-white/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-hidden transition-all placeholder:text-slate-300 font-medium"
                placeholder="Como prefere ser chamado?"
              />
              <p v-if="form.errors.nickname" class="text-xs text-red-500 mt-2 font-bold flex items-center gap-1">
                <Icon icon="lucide:alert-circle" class="size-3" /> {{ form.errors.nickname }}
              </p>
            </div>

            <div class="sm:col-span-2">
              <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Telefone</label>
              <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                    <Icon icon="lucide:phone" class="size-4" />
                </div>
                <input 
                  v-model="form.phone" 
                  type="tel"
                  class="w-full pl-10 pr-4 py-3 bg-white/50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-hidden transition-all placeholder:text-slate-300 font-medium"
                  placeholder="(11) 99999-8888"
                />
              </div>
              <p v-if="form.errors.phone" class="text-xs text-red-500 mt-2 font-bold flex items-center gap-1">
                <Icon icon="lucide:alert-circle" class="size-3" /> {{ form.errors.phone }}
              </p>
            </div>
          </div>
        </div>

        <!-- Privacy Settings -->
        <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-xl shadow-slate-200/50 border border-white/80 p-6 sm:p-8">
          <h2 class="text-xl font-black text-slate-900 mb-8 flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                <Icon icon="lucide:lock" class="size-5" />
            </span>
            Privacidade
          </h2>
          
          <div class="space-y-6">
            <label class="flex items-center justify-between cursor-pointer group p-4 rounded-2xl hover:bg-slate-50 transition-colors">
              <div class="flex items-center gap-4">
                <div class="size-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-blue-100 group-hover:text-blue-600 transition-colors">
                    <Icon icon="lucide:globe" class="size-5" />
                </div>
                <div>
                    <span class="font-black text-slate-900">Perfil Público</span>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mt-0.5">Permitir que outros vejam seu perfil</p>
                </div>
              </div>
              <div class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors" :class="form.preferences.privacy.public_profile ? 'bg-blue-600' : 'bg-slate-200'">
                <input v-model="form.preferences.privacy.public_profile" type="checkbox" class="sr-only">
                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow-md" :class="form.preferences.privacy.public_profile ? 'translate-x-6' : 'translate-x-1'"></span>
              </div>
            </label>

            <label class="flex items-center justify-between cursor-pointer group p-4 rounded-2xl hover:bg-slate-50 transition-colors">
              <div class="flex items-center gap-4">
                <div class="size-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-blue-100 group-hover:text-blue-600 transition-colors">
                    <Icon icon="lucide:bar-chart-3" class="size-5" />
                </div>
                <div>
                    <span class="font-black text-slate-900">Mostrar Progresso</span>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mt-0.5">Exibir desafios ativos no perfil</p>
                </div>
              </div>
              <div class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors" :class="form.preferences.privacy.show_progress ? 'bg-blue-600' : 'bg-slate-200'">
                <input v-model="form.preferences.privacy.show_progress" type="checkbox" class="sr-only">
                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow-md" :class="form.preferences.privacy.show_progress ? 'translate-x-6' : 'translate-x-1'"></span>
              </div>
            </label>
          </div>
        </div>

        <!-- Notifications -->
        <div class="bg-white/70 backdrop-blur-xl rounded-3xl shadow-xl shadow-slate-200/50 border border-white/80 p-6 sm:p-8">
          <h2 class="text-xl font-black text-slate-900 mb-8 flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-50 text-orange-600">
                <Icon icon="lucide:bell" class="size-5" />
            </span>
            Notificações
          </h2>
          
          <div class="space-y-6">
            <label class="flex items-center justify-between cursor-pointer group p-4 rounded-2xl hover:bg-slate-50 transition-colors">
              <div class="flex items-center gap-4">
                <div class="size-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-blue-100 group-hover:text-blue-600 transition-colors">
                    <Icon icon="lucide:mail" class="size-5" />
                </div>
                <div>
                    <span class="font-black text-slate-900">Email</span>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mt-0.5">Receber notificações por email</p>
                </div>
              </div>
              <div class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors" :class="form.preferences.notifications.email ? 'bg-blue-600' : 'bg-slate-200'">
                <input v-model="form.preferences.notifications.email" type="checkbox" class="sr-only">
                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow-md" :class="form.preferences.notifications.email ? 'translate-x-6' : 'translate-x-1'"></span>
              </div>
            </label>

            <label class="flex items-center justify-between cursor-not-allowed opacity-60 group p-4 rounded-2xl">
              <div class="flex items-center gap-4">
                <div class="size-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400">
                    <Icon icon="lucide:message-square" class="size-5" />
                </div>
                <div>
                    <span class="font-black text-slate-400">WhatsApp (DM)</span>
                    <p class="text-[10px] text-blue-600 font-black uppercase tracking-widest mt-1">⚠️ Desativado temporariamente</p>
                </div>
              </div>
              <div class="relative inline-flex h-6 w-11 items-center rounded-full bg-slate-100">
                <span class="inline-block h-4 w-4 transform rounded-full bg-slate-300 translate-x-1"></span>
              </div>
            </label>

            <label class="flex items-center justify-between cursor-pointer group p-4 rounded-2xl hover:bg-slate-50 transition-colors">
              <div class="flex items-center gap-4">
                <div class="size-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-blue-100 group-hover:text-blue-600 transition-colors">
                    <Icon icon="lucide:clock" class="size-5" />
                </div>
                <div>
                    <span class="font-black text-slate-900">Lembrete Diário</span>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mt-0.5">Receber alertas diários de check-in</p>
                </div>
              </div>
              <div class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors" :class="form.preferences.notifications.daily_reminder ? 'bg-blue-600' : 'bg-slate-200'">
                <input v-model="form.preferences.notifications.daily_reminder" type="checkbox" class="sr-only">
                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow-md" :class="form.preferences.notifications.daily_reminder ? 'translate-x-6' : 'translate-x-1'"></span>
              </div>
            </label>
          </div>
        </div>

        <!-- Plan Info -->
        <div class="bg-slate-900 rounded-3xl p-8 shadow-2xl shadow-slate-900/20 relative overflow-hidden">
          <div class="absolute -top-12 -right-12 w-48 h-48 bg-purple-600/20 rounded-full blur-3xl"></div>
          
          <div class="flex flex-col sm:flex-row items-center justify-between gap-8 relative z-10">
            <div class="text-center sm:text-left">
              <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-white text-[10px] font-black tracking-widest uppercase mb-4">
                <Icon icon="lucide:star" class="size-3 text-yellow-400 fill-yellow-400" />
                Plano Atual
              </div>
              <h3 class="text-3xl font-black text-white mb-2 leading-tight">
                {{ props.user?.is_pro ? 'DOPA Pro Experience' : 'DOPA Free Tier' }}
              </h3>
              <p v-if="props.user?.is_pro && props.user?.subscription_ends_at" class="text-slate-400 text-sm font-medium">
                Sua jornada premium é válida até <span class="text-white">{{ formatDate(props.user.subscription_ends_at) }}</span>
              </p>
              <p v-else class="text-slate-400 text-sm font-medium">
                Desbloqueie todo o potencial da sua produtividade.
              </p>
            </div>
            
            <Link 
              v-if="!props.user?.is_pro"
              href="/subscriptions/create"
              class="bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-600 text-white px-8 py-4 rounded-2xl font-black hover:scale-105 active:scale-95 transition-all shadow-xl shadow-blue-500/20 text-center"
            >
              Upgrade para PRO
            </Link>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4">
          <Link 
            href="/dopa"
            class="px-8 py-3 bg-white/50 backdrop-blur-sm border border-slate-200 rounded-2xl text-slate-600 font-black hover:bg-slate-100 transition-all text-center order-2 sm:order-1 active:scale-95"
          >
            Cancelar
          </Link>
          <button 
            type="submit"
            :disabled="submitting"
            class="bg-blue-600 text-white px-8 py-3 rounded-2xl font-black hover:bg-blue-700 transition-all shadow-lg shadow-blue-600/10 disabled:opacity-50 disabled:cursor-not-allowed order-1 sm:order-2 active:scale-95"
          >
            {{ submitting ? 'Salvando...' : 'Salvar Alterações' }}
          </button>
        </div>
      </form>
    </main>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import DopaHeader from '@/components/DopaHeader.vue'
import { useSeoMetaTags } from '@/composables/useSeoMetaTags.js'
import { Icon } from '@iconify/vue'

const props = defineProps({
  user: Object,
  whatsappSession: Object,
})

useSeoMetaTags({
  title: 'Configurações',
})

const form = useForm({
  username: props.user?.username || '',
  nickname: props.user?.nickname || '',
  phone: props.user?.phone || '',
  preferences: {
    privacy: {
      public_profile: props.user?.preferences?.privacy?.public_profile ?? true,
      show_progress: props.user?.preferences?.privacy?.show_progress ?? true,
    },
    notifications: {
      email: props.user?.preferences?.notifications?.email ?? true,
      whatsapp: false,
      daily_reminder: props.user?.preferences?.notifications?.daily_reminder ?? false,
    }
  }
})

const submitting = ref(false)

const submit = () => {
  submitting.value = true
  form
    .transform((data) => ({
      ...data,
      // Evita falhar validação quando o usuário deixa campos vazios
      username: (data.username || '').trim() || null,
      nickname: (data.nickname || '').trim() || null,
      phone: (data.phone || '').trim() || null,
    }))
    .patch('/profile/settings', {
    onFinish: () => {
      submitting.value = false
    }
  })
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('pt-BR')
}
</script>

