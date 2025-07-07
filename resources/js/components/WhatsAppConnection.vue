<template>
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
    <div class="flex items-start justify-between mb-4">
      <div class="flex items-center space-x-3">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
          <span class="text-2xl">📱</span>
        </div>
        <div>
          <h4 class="font-bold text-gray-900">WhatsApp Bot</h4>
          <p class="text-sm text-gray-600">Check-ins automáticos via foto + hashtag</p>
        </div>
      </div>

      <div class="flex items-center space-x-2">
        <span :class="[
          'w-3 h-3 rounded-full',
          isConnected ? 'bg-green-500' : 'bg-gray-300'
        ]"></span>
        <span :class="[
          'text-sm font-medium',
          isConnected ? 'text-green-700' : 'text-gray-500'
        ]">
          {{ connectionStatus }}
        </span>
      </div>
    </div>

    <!-- Connected State -->
    <div v-if="isConnected" class="space-y-4">
      <!-- Bot Info -->
      <div class="p-4 bg-green-50 rounded-xl border border-green-200">
        <div class="flex items-center justify-between mb-3">
          <div>
            <p class="font-semibold text-green-800">Como usar</p>
            <p class="text-lg font-mono text-green-700">{{ formatPhoneNumber(whatsappSession.bot_number) }}</p>
          </div>
          <a :href="whatsappLink" target="_blank"
            class="bg-green-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-700 transition-colors flex items-center space-x-2">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
              <path
                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.520-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488" />
            </svg>
            <span>Fazer check-in</span>
          </a>
        </div>

        <!-- Usage Instructions -->
        <div class="space-y-2">
          <div class="space-y-1 text-sm text-green-700">
            <p>1. Tire uma foto da sua atividade</p>
            <p>2. Envie com a hashtag da task (ex: #leitura)</p>
            <p>3. Receba confirmação automática ✅</p>
          </div>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 gap-4">
        <div class="p-3 bg-gray-50 rounded-lg text-center">
          <div class="text-xl font-bold text-gray-900">{{ whatsappSession.checkin_count || 0 }}</div>
          <div class="text-xs text-gray-500">Check-ins via WhatsApp</div>
        </div>
        <div class="p-3 bg-gray-50 rounded-lg text-center">
          <div class="text-xl font-bold text-gray-900">{{ whatsappSession.message_count || 0 }}</div>
          <div class="text-xs text-gray-500">Mensagens trocadas</div>
        </div>
      </div>

      <!-- Last Activity -->
      <div v-if="whatsappSession.last_activity" class="text-xs text-gray-500">
        Última atividade: {{ formatRelativeTime(whatsappSession.last_activity) }}
      </div>

      <!-- Disconnect Button -->
      <Button @click="handleDisconnect" :disabled="disconnecting" variant="destructive" size="lg"
        class="w-full cursor-pointer hover:bg-red-700 text-gray-600 hover:shadow-md transition-all duration-200">
        <svg v-if="disconnecting" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
          </path>
        </svg>
        <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
        <span>{{ disconnecting ? 'Desconectando...' : 'Desconectar WhatsApp' }}</span>
      </Button>
    </div>

    <!-- Disconnected State -->
    <div v-else class="space-y-4">
      <div class="p-4 bg-blue-50 rounded-xl border border-blue-200">
        <div class="flex items-start space-x-3">
          <div class="flex-shrink-0">
            <span class="text-blue-600 text-xl">💡</span>
          </div>
          <div class="text-sm text-blue-800">
            <p class="font-semibold mb-2">Conecte seu WhatsApp para:</p>
            <ul class="space-y-1 text-blue-700">
              <li>• Check-ins automáticos por foto</li>
              <li>• Confirmações em tempo real</li>
              <li>• Zero fricção - use o app que já tem</li>
            </ul>
          </div>
        </div>
      </div>

      <Button @click="handleConnect" :disabled="connecting" size="lg"
        class="w-full bg-green-600 hover:bg-green-700 text-white cursor-pointer hover:shadow-md transition-all duration-200">
        <svg v-if="connecting" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
          </path>
        </svg>
        <svg v-else class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
          <path
            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.520-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488" />
        </svg>
        <span>{{ connecting ? 'Conectando...' : 'Conectar WhatsApp' }}</span>
      </Button>

      <!-- Free Plan Limitation -->
      <div v-if="!user.is_pro" class="p-3 bg-amber-50 rounded-lg border border-amber-200">
        <div class="flex items-center space-x-2">
          <span class="text-amber-600">⚠️</span>
          <div class="text-sm text-amber-800">
            <span class="font-medium">Plano Free:</span> Check-ins manuais via WhatsApp.
            <Link href="/upgrade" class="text-amber-700 underline hover:text-amber-900">
            Upgrade para PRO
            </Link> para análise automática com IA.
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-xl p-6 shadow-lg w-full max-w-sm">
      <h3 class="text-lg font-semibold mb-2">Informe seu número do WhatsApp</h3>
      <input v-model="phone" type="tel" placeholder="Ex: 11999998888" class="w-full border rounded-lg px-3 py-2 mb-4" />
      <div class="flex justify-end space-x-2">
        <Button @click="showModal = false" variant="outline" size="sm">
          Cancelar
        </Button>
        <Button @click="handleSubmitPhone" :disabled="connecting" size="sm" class="bg-green-600 hover:bg-green-700">
          <svg v-if="connecting" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
          </svg>
          <span>{{ connecting ? 'Conectando...' : 'Conectar' }}</span>
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})
// Emits
const emit = defineEmits(['connection-updated'])

// State
const connecting = ref(false)
const disconnecting = ref(false)
const showModal = ref(false)
const phone = ref(props.user.whatsapp_number || '')

// Estado reativo do WhatsApp
const isConnected = ref(false)
const whatsappSession = ref({})

const connectionStatus = computed(() => {
  if (!isConnected.value) return 'Desconectado'
  return 'Conectado'
})

const whatsappLink = computed(() => {
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
        phone.value = data.session.phone_number
      } else if (props.user.phone || props.user.whatsapp_number) {
        phone.value = props.user.phone || props.user.whatsapp_number
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
  if (phone.value.match(/^\d{10,15}$/)) {
    connecting.value = true
    try {
      const response = await fetch('/whatsapp/connect', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ phone_number: phone.value })
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
    showModal.value = true
  }
}

const handleSubmitPhone = async () => {
  if (!phone.value.match(/^\d{10,15}$/)) {
    alert('Digite um número de WhatsApp válido.')
    return
  }
  connecting.value = true
  try {
    const response = await fetch('/whatsapp/connect', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ phone_number: phone.value })
    })
    if (response.ok) {
      const data = await response.json()
      if (data.success && data.whatsapp_url) {
        showModal.value = false
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
    const response = await fetch('/whatsapp/disconnect', {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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