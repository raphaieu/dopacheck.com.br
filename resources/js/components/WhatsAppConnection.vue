<template>
  <div class="bg-white/70 backdrop-blur-xl rounded-3xl p-6 md:p-8 border border-white/80 shadow-xl shadow-blue-500/5 relative overflow-hidden group">
    <div class="absolute -top-24 -right-24 w-64 h-64 bg-emerald-400/5 rounded-full blur-3xl group-hover:bg-emerald-400/10 transition-colors duration-500"></div>
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8 relative z-10">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 bg-emerald-100 rounded-2xl flex items-center justify-center border border-emerald-200 shadow-sm shadow-emerald-200/50">
          <Icon icon="lucide:message-circle" class="size-8 text-emerald-600" />
        </div>
        <div>
          <h4 class="text-xl font-black text-slate-900 tracking-tight leading-none">WhatsApp Bot</h4>
          <p class="text-sm text-slate-500 font-medium mt-1">Check-ins automáticos via foto + hashtag</p>
        </div>
      </div>

      <div class="flex items-center gap-2 bg-white/50 backdrop-blur-sm self-start md:self-auto px-3 py-1.5 rounded-full border border-white/80 shadow-sm">
        <span :class="[
          'w-2.5 h-2.5 rounded-full animate-pulse',
          isConnected ? 'bg-emerald-500 shadow-lg shadow-emerald-500/50' : 'bg-slate-300'
        ]"></span>
        <span :class="[
          'text-xs font-bold uppercase tracking-widest',
          isConnected ? 'text-emerald-700' : 'text-slate-500'
        ]">
          {{ connectionStatus }}
        </span>
      </div>
    </div>

    <!-- Connected State -->
    <div v-if="isConnected" class="space-y-6 relative z-10">
      <!-- Bot Info -->
      <div class="p-6 bg-emerald-500/5 rounded-3xl border border-emerald-500/10 relative overflow-hidden">
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl"></div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 relative z-10">
          <div>
            <p class="text-[10px] font-black text-emerald-700 uppercase tracking-[0.2em] mb-2 opacity-60">Número do Bot</p>
            <p class="text-2xl font-black text-slate-900 tracking-tight leading-none">{{ formatPhoneNumber(whatsappSession.bot_number) }}</p>
          </div>
          <a :href="whatsappLink" target="_blank"
            class="bg-emerald-600 text-white px-8 py-4 rounded-2xl font-black hover:bg-emerald-500 hover:-translate-y-1 transition-all shadow-xl shadow-emerald-600/20 flex items-center justify-center gap-2 active:scale-95">
            <Icon icon="lucide:send" class="size-5" />
            <span>Check-in Agora</span>
          </a>
        </div>

        <!-- Usage Instructions -->
        <div class="mt-8 pt-8 border-t border-emerald-500/10">
          <p class="text-[10px] font-black text-emerald-700 uppercase tracking-[0.2em] mb-4 opacity-60">Instruções rápidas</p>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="flex items-center gap-3 bg-white/40 p-3 rounded-xl border border-white/60">
              <div class="size-8 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-600 font-black text-sm">1</div>
              <p class="text-xs font-bold text-slate-700 leading-tight">Tire uma foto da sua atividade</p>
            </div>
            <div class="flex items-center gap-3 bg-white/40 p-3 rounded-xl border border-white/60">
              <div class="size-8 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-600 font-black text-sm">2</div>
              <p class="text-xs font-bold text-slate-700 leading-tight">Envie com a #hashtag na legenda</p>
            </div>
            <div class="flex items-center gap-3 bg-white/40 p-3 rounded-xl border border-white/60">
              <div class="size-8 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-600 font-black text-sm">3</div>
              <p class="text-xs font-bold text-slate-700 leading-tight">Receba a confirmação na hora</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Info -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
        <div class="flex items-center gap-6">
          <div class="group/stat">
            <div class="text-xl font-black text-slate-900 tracking-tight leading-none">{{ whatsappSession.checkin_count ?? 0 }}</div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Check-ins via WhatsApp</div>
          </div>
          <div v-if="whatsappSession.last_activity" class="text-right sm:text-left">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Última Atividade</div>
            <div class="text-xs font-black text-slate-900 tracking-tight">{{ formatRelativeTime(whatsappSession.last_activity) }}</div>
          </div>
        </div>

        <button @click="handleDisconnect" :disabled="disconnecting"
          class="cursor-pointer text-slate-400 hover:text-red-600 text-xs font-bold uppercase tracking-widest flex items-center justify-center gap-2 px-4 py-2 rounded-xl hover:bg-red-50 transition-all disabled:opacity-50">
          <Icon v-if="disconnecting" icon="lucide:loader-2" class="size-4 animate-spin" />
          <Icon v-else icon="lucide:log-out" class="size-4" />
          <span>{{ disconnecting ? 'Desconectando...' : 'Desconectar WhatsApp' }}</span>
        </button>
      </div>
    </div>

    <!-- Disconnected State -->
    <div v-else class="space-y-6 relative z-10">
      <div class="p-6 bg-blue-500/5 rounded-3xl border border-blue-500/10 relative overflow-hidden">
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-blue-500/10 rounded-full blur-2xl"></div>
        <div class="flex flex-col sm:flex-row items-center gap-6 relative z-10">
          <div class="bg-white p-4 rounded-2xl shadow-xl shadow-blue-500/5 border border-blue-100 flex-shrink-0 animate-bounce transition-all duration-1000">
            <Icon icon="lucide:zap" class="size-8 text-blue-600" />
          </div>
          <div>
            <p class="text-xl font-black text-slate-900 tracking-tight leading-snug">Conecte seu WhatsApp para ter zero fricção!</p>
            <p class="text-sm text-slate-600 font-medium mt-1">Automatize seus check-ins enviando fotos diretamente no app que você já usa todo dia.</p>
          </div>
        </div>
      </div>

      <button @click="handleConnect" :disabled="connecting"
        class="cursor-pointer w-full bg-slate-900 text-white py-4 rounded-2xl font-black hover:bg-slate-800 hover:-translate-y-1 transition-all shadow-xl shadow-slate-900/10 flex items-center justify-center gap-3 active:scale-[0.98]">
        <Icon v-if="connecting" icon="lucide:loader-2" class="size-6 animate-spin" />
        <Icon v-else icon="lucide:external-link" class="size-6 text-blue-400" />
        <span>{{ connecting ? 'Conectando...' : 'Conectar agora' }}</span>
      </button>

      <!-- Free Plan Limitation -->
      <div v-if="!user.is_pro" class="p-4 bg-amber-500/5 rounded-2xl border border-amber-500/10 flex items-start gap-3">
        <Icon icon="lucide:alert-triangle" class="size-5 text-amber-600 flex-shrink-0 mt-0.5" />
        <div class="text-xs font-medium text-amber-900 leading-relaxed">
          <span class="font-black uppercase tracking-widest text-[9px] block mb-1 opacity-60">Plano Standard</span>
          No plano free você faz check-ins manuais pelo WhatsApp. 
          <Link href="/upgrade" class="text-amber-700 font-black underline hover:text-amber-900 ml-1">
            Vire PRO
          </Link> para análise automática de atividade com Visão Computacional.
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para coletar telefone (Teleported) -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showPhoneModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[110] flex items-center justify-center p-4">
          <div class="bg-white/95 backdrop-blur-2xl rounded-[2.5rem] shadow-2xl p-8 max-w-md w-full relative border border-white/50 animate-in zoom-in-95 duration-300">
            <button @click="showPhoneModal = false" class="cursor-pointer absolute top-6 right-6 text-slate-400 hover:text-slate-900 bg-slate-100 p-2 rounded-full transition-all">
              <Icon icon="lucide:x" class="size-6" />
            </button>
            <div class="text-center mb-8">
              <div class="size-20 mx-auto bg-blue-100 rounded-3xl flex items-center justify-center text-blue-600 mb-4 shadow-inner">
                <Icon icon="lucide:phone" class="size-10" />
              </div>
              <h3 class="text-2xl font-black text-slate-900 tracking-tight">Conectar WhatsApp</h3>
              <p class="text-slate-500 font-medium mt-1">Digite seu número com DDD (apenas números)</p>
            </div>
            
            <div class="space-y-6">
              <div class="relative group">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-600 transition-colors">
                  <Icon icon="lucide:smartphone" class="size-5" />
                </div>
                <input v-model="phoneNumber" type="tel" placeholder="Ex: 5511999999999"
                  class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-black text-lg tracking-tight placeholder:font-medium placeholder:text-slate-300"
                  @keyup.enter="startConnection" />
              </div>
              
              <button @click="startConnection" :disabled="connecting"
                class="cursor-pointer w-full bg-slate-900 text-white py-5 rounded-2xl font-black uppercase tracking-widest text-[11px] hover:bg-slate-800 disabled:opacity-50 transition-all shadow-xl shadow-slate-900/10 flex items-center justify-center gap-3 active:scale-[0.98]">
                <Icon v-if="connecting" icon="lucide:loader-2" class="size-5 animate-spin" />
                <Icon v-else icon="lucide:zap" class="size-5 text-blue-400" />
                <span>{{ connecting ? 'Iniciando...' : 'Conectar agora' }}</span>
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { Button } from '@/components/ui/button'
import { csrfFetch } from '@/utils/csrf.js'

const props = defineProps({
  user: {
    type: Object,
    required: true
  },
  currentChallenge: {
    type: Object,
    default: null
  }
})
// Emits
const emit = defineEmits(['connection-updated'])

// State
const connecting = ref(false)
const disconnecting = ref(false)
const showPhoneModal = ref(false)
const phoneNumber = ref(props.user.whatsapp_number || '')

// Estado reativo do WhatsApp
const isConnected = ref(false)
const whatsappSession = ref({})

const connectionStatus = computed(() => {
  if (!isConnected.value) return 'Desconectado'
  return 'Conectado'
})

const whatsappLink = computed(() => {
  if (props.currentChallenge?.whatsapp_checkin_url) {
    return props.currentChallenge.whatsapp_checkin_url
  }
  return `https://wa.me/5571993676365`
})

// Buscar status da conexão na API
const fetchStatus = async () => {
  try {
    const response = await fetch('/api/whatsapp-status', {
      headers: { 'Accept': 'application/json' }
    })
    if (response.ok) {
      const data = await response.json()
      isConnected.value = data.connected
      whatsappSession.value = data.session || {}
      // Só atualize se vier do backend, senão mantenha o que já tem
      if (data.session?.phone_number) {
        phoneNumber.value = data.session.phone_number
      } else if (props.user.phone || props.user.whatsapp_number) {
        phoneNumber.value = props.user.phone || props.user.whatsapp_number
      }
    } else {
      isConnected.value = false
      whatsappSession.value = {}
    }
  } catch (e) {
    isConnected.value = false
    whatsappSession.value = {}
  }
}

onMounted(fetchStatus)

const handleConnect = async () => {
  if (phoneNumber.value.match(/^\d{10,15}$/)) {
    connecting.value = true
    try {
      const response = await csrfFetch('/whatsapp/connect', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ phone_number: phoneNumber.value })
      })
      if (response.ok) {
        const data = await response.json()
        if (data.success && data.whatsapp_url) {
          window.open(data.whatsapp_url, '_blank')
          await fetchStatus()
          emit('connection-updated', { is_active: true })
        }
      } else {
        throw new Error('Erro na resposta do servidor')
      }
    } catch (error) {
      console.error('Erro:', error)
      alert('Erro ao conectar WhatsApp. Tente novamente.')
    } finally {
      connecting.value = false
    }
  } else {
    showPhoneModal.value = true
  }
}

const startConnection = async () => {
  if (!phoneNumber.value.match(/^\d{10,15}$/)) {
    alert('Digite um número de WhatsApp válido.')
    return
  }
  connecting.value = true
  try {
    const response = await csrfFetch('/whatsapp/connect', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ phone_number: phoneNumber.value })
    })
    if (response.ok) {
      const data = await response.json()
      if (data.success && data.whatsapp_url) {
        showPhoneModal.value = false
        await fetchStatus()
        emit('connection-updated', { is_active: true })
        window.open(data.whatsapp_url, '_blank')
      }
    } else {
      throw new Error('Erro na resposta do servidor')
    }
  } catch (error) {
    console.error('Erro:', error)
    alert('Erro ao conectar WhatsApp. Tente novamente.')
  } finally {
    connecting.value = false
  }
}

const handleDisconnect = async () => {
  if (!confirm('Tem certeza que deseja desconectar o WhatsApp?')) return
  disconnecting.value = true
  try {
    const response = await csrfFetch('/whatsapp/disconnect', {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })
    await fetchStatus()
    emit('connection-updated', { is_active: false })
  } catch (error) {
    console.error('Erro:', error)
    alert('Erro ao desconectar WhatsApp. Tente novamente.')
  } finally {
    disconnecting.value = false
  }
}

const formatPhoneNumber = (phone) => {
  if (!phone) return ''
  const cleaned = phone.replace(/\D/g, '')
  if (cleaned.length === 13) {
    return `+${cleaned.slice(0, 2)} (${cleaned.slice(2, 4)}) ${cleaned.slice(4, 9)}-${cleaned.slice(9)}`
  }
  return phone
}

const formatRelativeTime = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now.getTime() - date.getTime()
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMins / 60)
  const diffDays = Math.floor(diffHours / 24)

  if (diffMins < 1) return 'agora mesmo'
  if (diffMins < 60) return `${diffMins}min atrás`
  if (diffHours < 24) return `${diffHours}h atrás`
  if (diffDays < 7) return `${diffDays}d atrás`

  return date.toLocaleDateString('pt-BR')
}
</script>