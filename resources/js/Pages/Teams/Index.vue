<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 relative overflow-x-clip pt-28 pb-24">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl pointer-events-none -z-10"></div>
        <div class="absolute top-1/2 -left-24 w-96 h-96 bg-purple-400/10 rounded-full blur-3xl pointer-events-none -z-10" style="animation-delay: 2s"></div>

        <DopaHeaderWrapper subtitle="Meus times" max-width="2xl" home-link="/dopa" :show-back-button="true" back-link="/dopa" />

        <main class="max-w-2xl mx-auto px-4 relative z-10">
            <!-- Flash success (ex.: após criar time vindo daqui) -->
            <p v-if="flashSuccess" class="mb-6 p-4 rounded-2xl bg-emerald-50 text-emerald-800 text-sm font-medium">
                {{ flashSuccess }}
            </p>

            <!-- Criar novo time -->
            <div class="mb-8">
                <Link :href="route('teams.create-from-challenge') + '?return_to=meus-times'"
                    class="inline-flex items-center gap-2 px-6 py-4 bg-slate-900 text-white font-black uppercase tracking-widest text-xs rounded-2xl hover:bg-slate-800 transition-colors shadow-lg shadow-slate-900/10">
                    <Icon icon="lucide:plus" class="size-5" />
                    Criar novo time
                </Link>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mt-2">
                    Você também pode criar um time ao criar um desafio (escopo "Novo time").
                </p>
            </div>

            <!-- Lista de times -->
            <div v-if="teams.length === 0" class="bg-white/80 backdrop-blur-xl rounded-3xl p-8 shadow-xl border border-white/80 text-center text-slate-500">
                <Icon icon="lucide:users" class="size-12 mx-auto mb-4 opacity-50" />
                <p class="font-bold">Você ainda não faz parte de nenhum time.</p>
                <p class="text-sm mt-2">Crie um time acima ou aceite um convite.</p>
            </div>

            <ul v-else class="space-y-4">
                <li v-for="team in teams" :key="team.id"
                    class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-xl border border-white/80 overflow-hidden">
                    <div class="p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-lg font-black text-slate-900 truncate">{{ team.name }}</h3>
                                    <span v-if="team.personal_team" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 uppercase">Pessoal</span>
                                    <span v-else-if="team.is_owner" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 uppercase">Dono</span>
                                </div>
                                <p v-if="team.slug" class="text-xs text-slate-500 font-medium mt-1">
                                    /join/{{ team.slug }}
                                </p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-2">
                                    {{ team.members_count }} {{ team.members_count === 1 ? 'membro' : 'membros' }}
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <a v-if="team.join_url" :href="team.join_url" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition-colors">
                                    <Icon icon="lucide:link" class="size-3.5" />
                                    Link de inscrição
                                </a>
                                <template v-if="team.is_owner && !team.personal_team">
                                    <Link :href="route('teams.edit', team.id)"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-50 text-blue-700 text-xs font-bold hover:bg-blue-100 transition-colors">
                                        <Icon icon="lucide:pencil" class="size-3.5" />
                                        Editar
                                    </Link>
                                    <button type="button" @click="confirmDelete(team)"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-rose-50 text-rose-700 text-xs font-bold hover:bg-rose-100 transition-colors">
                                        <Icon icon="lucide:trash-2" class="size-3.5" />
                                        Excluir
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </main>

        <!-- Modal confirmar exclusão -->
        <div v-if="teamToDelete" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="teamToDelete = null">
            <div class="bg-white rounded-3xl shadow-2xl p-6 max-w-sm w-full" @click.stop>
                <h3 class="text-lg font-black text-slate-900 mb-2">Excluir time?</h3>
                <p class="text-sm text-slate-600 mb-6">
                    O time <strong>{{ teamToDelete.name }}</strong> e todas as inscrições associadas serão removidos. Esta ação não pode ser desfeita.
                </p>
                <div class="flex gap-3 justify-end">
                    <button type="button" @click="teamToDelete = null"
                        class="px-4 py-2 rounded-xl font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                        Cancelar
                    </button>
                    <button type="button" @click="doDelete"
                        class="px-4 py-2 rounded-xl font-bold bg-rose-600 text-white hover:bg-rose-700 transition-colors disabled:opacity-50"
                        :disabled="deleting">
                        {{ deleting ? 'Excluindo...' : 'Excluir' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import DopaHeaderWrapper from '@/components/DopaHeaderWrapper.vue'

const props = defineProps({
    teams: {
        type: Array,
        default: () => [],
    },
})

const page = usePage()
const flashSuccess = computed(() => page.props.flash?.success)

const teamToDelete = ref(null)
const deleting = ref(false)

function confirmDelete(team) {
    teamToDelete.value = team
}

function doDelete() {
    if (!teamToDelete.value) return
    deleting.value = true
    router.delete(route('teams.destroy', teamToDelete.value.id), {
        onFinish: () => {
            deleting.value = false
            teamToDelete.value = null
        },
    })
}
</script>
