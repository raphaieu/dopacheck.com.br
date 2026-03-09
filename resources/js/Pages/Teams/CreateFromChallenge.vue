<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 relative overflow-x-clip pt-28">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl animate-pulse pointer-events-none"></div>
        <div class="absolute top-1/2 -left-24 w-96 h-96 bg-purple-400/10 rounded-full blur-3xl animate-pulse pointer-events-none" style="animation-delay: 2s"></div>

        <DopaHeaderWrapper subtitle="Novo time (grupo)" max-width="2xl" />

        <main class="max-w-2xl mx-auto px-4 pb-24 relative z-10">
            <!-- Bloqueio: WhatsApp não vinculado -->
            <div v-if="whatsapp_required" class="relative bg-white/90 backdrop-blur-xl rounded-[2rem] p-8 shadow-xl border border-amber-100 overflow-hidden">
                <p v-if="flashError" class="mb-6 p-4 rounded-2xl bg-rose-50 text-rose-800 text-sm font-medium">
                    {{ flashError }}
                </p>
                <div class="text-center space-y-6">
                    <div class="size-16 rounded-2xl bg-amber-100 flex items-center justify-center mx-auto">
                        <Icon icon="lucide:message-circle-off" class="size-8 text-amber-600" />
                    </div>
                    <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">WhatsApp obrigatório</h2>
                    <p class="text-slate-600 text-sm max-w-md mx-auto">
                        Para criar um time para grupo, você precisa ter seu WhatsApp vinculado. Envie uma mensagem no privado para o bot para vincular — assim conseguimos correlacionar o grupo ao seu time quando você adicionar o bot.
                    </p>
                    <button
                        type="button"
                        @click="handleConnect"
                        :disabled="connecting"
                        class="cursor-pointer inline-flex items-center gap-2 px-6 py-4 bg-emerald-600 text-white font-black uppercase tracking-widest text-xs rounded-2xl hover:bg-emerald-700 transition-colors disabled:opacity-60"
                    >
                        <Icon v-if="connecting" icon="lucide:loader-2" class="size-5 animate-spin" />
                        <Icon v-else icon="lucide:link" class="size-5" />
                        <span>{{ connecting ? 'Conectando...' : 'Conectar WhatsApp' }}</span>
                    </button>
                    <p class="text-xs text-slate-500">
                        <Link :href="route('challenges.create')" class="underline hover:text-slate-700">Voltar para Criar desafio</Link>
                    </p>
                </div>
            </div>

            <!-- Formulário de cadastro do time -->
            <form v-else @submit.prevent="form.post(route('teams.create-from-challenge.store'))" class="space-y-8">
                <div class="relative bg-white/90 backdrop-blur-xl rounded-[2rem] p-8 shadow-xl border border-white/80 overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-600 via-violet-600 to-purple-600 opacity-80"></div>
                    <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-2">Dados do time</h2>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-8">Link de inscrição: /join/{{ form.slug || 'seu-slug' }}</p>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nome do time *</label>
                            <input v-model="form.name" type="text" required maxlength="255"
                                class="w-full px-5 py-4 bg-slate-50/50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-200 font-medium text-slate-900"
                                placeholder="Ex: SalvaDopamina" />
                            <p v-if="form.errors.name" class="mt-1 text-[10px] font-black text-rose-600">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Slug (URL) *</label>
                            <input v-model="form.slug" type="text" required maxlength="255" pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                                class="w-full px-5 py-4 bg-slate-50/50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-200 font-medium text-slate-900"
                                placeholder="salvadopamina" @input="form.slug = form.slug.toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-')" />
                            <p class="mt-1 text-[10px] text-slate-500">Apenas letras minúsculas, números e hífens.</p>
                            <p v-if="form.errors.slug" class="mt-1 text-[10px] font-black text-rose-600">{{ form.errors.slug }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Link do grupo (WhatsApp)</label>
                            <input v-model="form.whatsapp_join_url" type="url" maxlength="2048"
                                class="w-full px-5 py-4 bg-slate-50/50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-200 font-medium text-slate-900"
                                placeholder="https://chat.whatsapp.com/..." />
                            <p v-if="form.errors.whatsapp_join_url" class="mt-1 text-[10px] font-black text-rose-600">{{ form.errors.whatsapp_join_url }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">JID do grupo</label>
                            <input v-model="form.whatsapp_group_jid" type="text" maxlength="64"
                                class="w-full px-5 py-4 bg-slate-50/50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-200 font-medium text-slate-900"
                                placeholder="120363404774829500@g.us" />
                            <p class="mt-1 text-[10px] text-slate-500">Pode ser preenchido depois, quando o bot for adicionado ao grupo.</p>
                            <p v-if="form.errors.whatsapp_group_jid" class="mt-1 text-[10px] font-black text-rose-600">{{ form.errors.whatsapp_group_jid }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nome do grupo (opcional)</label>
                            <input v-model="form.whatsapp_group_name" type="text" maxlength="255"
                                class="w-full px-5 py-4 bg-slate-50/50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-200 font-medium text-slate-900"
                                placeholder="Nome como aparece no WhatsApp" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Título do onboarding (opcional)</label>
                            <input v-model="form.onboarding_title" type="text" maxlength="255"
                                class="w-full px-5 py-4 bg-slate-50/50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-200 font-medium text-slate-900"
                                placeholder="🎈 Formulário de Inscrição" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Texto de apresentação (opcional)</label>
                            <textarea v-model="form.onboarding_body" rows="4"
                                class="w-full px-5 py-4 bg-slate-50/50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-200 font-medium text-slate-900 resize-none"
                                placeholder="Texto que aparece na página de inscrição /join/..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Aviso: página padrão + Falar com Raphael -->
                <div class="rounded-2xl bg-slate-100/80 border border-slate-200 p-6">
                    <p class="text-sm text-slate-700 mb-4">
                        A página de inscrição será <strong>padrão</strong>: no máximo você pode ajustar cores e o texto acima. Quem se inscrever preenche nome, e-mail e WhatsApp e já é criado e vinculado ao time.
                    </p>
                    <p class="text-sm text-slate-600">
                        Quer algo mais personalizado (imagens, layout, etc.)? Entre em contato:
                        <a :href="whatsappRaphaelUrl" target="_blank" rel="noopener" class="inline-flex items-center gap-1 font-bold text-emerald-600 hover:text-emerald-700">
                            <Icon icon="lucide:message-circle" class="size-4" />
                            Falar com Raphael no WhatsApp
                        </a>
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 justify-between items-center">
                    <Link :href="route('challenges.create')" class="text-sm font-bold text-slate-500 hover:text-slate-700 uppercase tracking-widest">
                        Cancelar
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="px-8 py-4 bg-slate-900 text-white font-black uppercase tracking-widest text-xs rounded-2xl hover:bg-slate-800 disabled:opacity-50 transition-colors">
                        {{ form.processing ? 'Criando...' : 'Criar time e voltar ao desafio' }}
                    </button>
                </div>
            </form>
        </main>
    </div>

    <!-- Modal para coletar telefone (reaproveita fluxo do componente de conexão) -->
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="showPhoneModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[110] flex items-center justify-center p-4">
                <div class="bg-white/95 backdrop-blur-2xl rounded-[2.5rem] shadow-2xl p-8 max-w-md w-full relative border border-white/50 animate-in zoom-in-95 duration-300">
                    <button
                        type="button"
                        @click="showPhoneModal = false"
                        class="cursor-pointer absolute top-6 right-6 text-slate-400 hover:text-slate-900 bg-slate-100 p-2 rounded-full transition-all"
                    >
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
                            <input
                                v-model="phoneNumber"
                                type="tel"
                                placeholder="Ex: 5511999999999"
                                class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-black text-lg tracking-tight placeholder:font-medium placeholder:text-slate-300"
                                @keyup.enter="startConnection"
                            />
                        </div>

                        <button
                            type="button"
                            @click="startConnection"
                            :disabled="connecting"
                            class="cursor-pointer w-full bg-slate-900 text-white py-5 rounded-2xl font-black uppercase tracking-widest text-[11px] hover:bg-slate-800 disabled:opacity-50 transition-all shadow-xl shadow-slate-900/10 flex items-center justify-center gap-3 active:scale-[0.98]"
                        >
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
import { useForm, Link, usePage, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import DopaHeaderWrapper from '@/components/DopaHeaderWrapper.vue'
import { csrfFetch } from '@/utils/csrf.js'

const props = defineProps({
    whatsapp_required: { type: Boolean, default: false },
    whatsapp_connect_url: { type: String, default: '' },
})

const page = usePage()
const flashError = computed(() => page.props.flash?.error)

const form = useForm({
    name: '',
    slug: '',
    whatsapp_join_url: '',
    whatsapp_group_jid: '',
    whatsapp_group_name: '',
    onboarding_title: '',
    onboarding_body: '',
})

const whatsappRaphaelUrl = computed(() => {
    const text = encodeURIComponent('Olá, vim pelo DOPA Check – quero personalizar a landing do meu time.')
    return `https://wa.me/5511948863848?text=${text}`
})

// Estado de conexão WhatsApp (reuso simplificado do componente WhatsAppConnection)
const connecting = ref(false)
const showPhoneModal = ref(false)
const phoneNumber = ref('')

onMounted(() => {
    const user = page.props.auth?.user || {}
    if (typeof user.whatsapp_number === 'string' && user.whatsapp_number.trim() !== '') {
        phoneNumber.value = user.whatsapp_number
    } else if (typeof user.phone === 'string' && user.phone.trim() !== '') {
        phoneNumber.value = user.phone
    }
})

const handleConnect = async () => {
    if (phoneNumber.value.match(/^\d{10,15}$/)) {
        await startConnection()
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
                'Accept': 'application/json',
            },
            body: JSON.stringify({ phone_number: phoneNumber.value }),
        })
        if (!response.ok) {
            throw new Error('Erro na resposta do servidor')
        }
        const data = await response.json()
        if (data.success && data.whatsapp_url) {
            showPhoneModal.value = false
            // Abre o WhatsApp em nova aba para o usuário enviar a DM de confirmação
            window.open(data.whatsapp_url, '_blank')
            // Recarrega a página para reavaliar o bloqueio (usa sessão Redis / whatsapp_confirmed)
            router.visit(route('teams.create-from-challenge'))
        }
    } catch (error) {
        console.error('Erro ao conectar WhatsApp:', error)
        alert('Erro ao conectar WhatsApp. Tente novamente.')
    } finally {
        connecting.value = false
    }
}
</script>
