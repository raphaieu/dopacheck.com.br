<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <!-- Header -->
    <DopaHeader subtitle="Configurações" max-width="4xl" home-link="/dopa" :show-back-button="true" back-link="/dopa" />

    <main class="max-w-4xl mx-auto px-4 py-8">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Profile Information -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <h2 class="text-xl font-bold text-gray-900 mb-6">Informações do Perfil</h2>
          
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nome de usuário</label>
              <input 
                v-model="form.username" 
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="seu_usuario"
              />
              <p class="text-xs text-gray-500 mt-1">Usado na URL do seu perfil público: /u/seu_usuario</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
              <input 
                v-model="form.phone" 
                type="tel"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="(11) 99999-8888"
              />
            </div>
          </div>
        </div>

        <!-- Privacy Settings -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <h2 class="text-xl font-bold text-gray-900 mb-6">Privacidade</h2>
          
          <div class="space-y-4">
            <label class="flex items-center justify-between cursor-pointer">
              <div>
                <span class="font-medium text-gray-900">Perfil Público</span>
                <p class="text-sm text-gray-600">Permitir que outros vejam seu perfil público</p>
              </div>
              <input 
                v-model="form.preferences.privacy.public_profile" 
                type="checkbox"
                class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500"
              />
            </label>

            <label class="flex items-center justify-between cursor-pointer">
              <div>
                <span class="font-medium text-gray-900">Mostrar Progresso</span>
                <p class="text-sm text-gray-600">Exibir desafios ativos no perfil público</p>
              </div>
              <input 
                v-model="form.preferences.privacy.show_progress" 
                type="checkbox"
                class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500"
              />
            </label>
          </div>
        </div>

        <!-- Notifications -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <h2 class="text-xl font-bold text-gray-900 mb-6">Notificações</h2>
          
          <div class="space-y-4">
            <label class="flex items-center justify-between cursor-pointer">
              <div>
                <span class="font-medium text-gray-900">Email</span>
                <p class="text-sm text-gray-600">Receber notificações por email</p>
              </div>
              <input 
                v-model="form.preferences.notifications.email" 
                type="checkbox"
                class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500"
              />
            </label>

            <label class="flex items-center justify-between cursor-pointer">
              <div>
                <span class="font-medium text-gray-900">WhatsApp</span>
                <p class="text-sm text-gray-600">Receber notificações via WhatsApp</p>
              </div>
              <input 
                v-model="form.preferences.notifications.whatsapp" 
                type="checkbox"
                class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500"
              />
            </label>

            <label class="flex items-center justify-between cursor-pointer">
              <div>
                <span class="font-medium text-gray-900">Lembrete Diário</span>
                <p class="text-sm text-gray-600">Receber lembretes diários de check-in</p>
              </div>
              <input 
                v-model="form.preferences.notifications.daily_reminder" 
                type="checkbox"
                class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500"
              />
            </label>
          </div>
        </div>

        <!-- Plan Info -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <h2 class="text-xl font-bold text-gray-900 mb-4">Plano Atual</h2>
          <div class="flex items-center justify-between">
            <div>
              <span :class="[
                'px-3 py-1 rounded-full text-sm font-medium',
                props.user?.is_pro ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600'
              ]">
                {{ props.user?.is_pro ? '✨ PRO' : '🆓 FREE' }}
              </span>
              <p v-if="props.user?.is_pro && props.user?.subscription_ends_at" class="text-sm text-gray-600 mt-2">
                Válido até {{ formatDate(props.user.subscription_ends_at) }}
              </p>
            </div>
            <Link 
              v-if="!props.user?.is_pro"
              href="/subscriptions"
              class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-2 rounded-lg font-medium hover:from-blue-700 hover:to-purple-700 transition-colors"
            >
              Upgrade para PRO
            </Link>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-4">
          <Link 
            href="/dopa"
            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors"
          >
            Cancelar
          </Link>
          <button 
            type="submit"
            :disabled="submitting"
            class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
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

const props = defineProps({
  user: Object,
  whatsappSession: Object,
})

const form = useForm({
  username: props.user?.username || '',
  phone: props.user?.phone || '',
  preferences: {
    privacy: {
      public_profile: props.user?.preferences?.privacy?.public_profile ?? true,
      show_progress: props.user?.preferences?.privacy?.show_progress ?? true,
    },
    notifications: {
      email: props.user?.preferences?.notifications?.email ?? true,
      whatsapp: props.user?.preferences?.notifications?.whatsapp ?? false,
      daily_reminder: props.user?.preferences?.notifications?.daily_reminder ?? false,
    }
  }
})

const submitting = ref(false)

const submit = () => {
  submitting.value = true
  form.patch('/profile/settings', {
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

