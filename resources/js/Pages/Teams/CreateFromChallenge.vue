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
                    <a :href="whatsapp_connect_url"
                        class="inline-flex items-center gap-2 px-6 py-4 bg-emerald-600 text-white font-black uppercase tracking-widest text-xs rounded-2xl hover:bg-emerald-700 transition-colors">
                        <Icon icon="lucide:link" class="size-5" />
                        Conectar WhatsApp
                    </a>
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
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import DopaHeaderWrapper from '@/components/DopaHeaderWrapper.vue'

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
</script>
