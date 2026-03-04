<script setup>
/**
 * Exemplo de landing customizada.
 * Copie este arquivo (ex.: MinhaComunidade.vue), estilize como quiser e
 * selecione o nome no Filament (Team → Página custom).
 *
 * A submissão deve ser POST para route('teams.join.store', team.slug).
 * Inclua os campos do form_schema do time e, se onboarding_behavior === 'create_user',
 * inclua terms_accepted: true e o checkbox de termos na UI.
 */
import { computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import WebLayout from '@/layouts/WebLayout.vue'

const props = defineProps({
  team: { type: Object, required: true },
})

const schema = computed(() => Array.isArray(props.team?.form_schema) ? props.team.form_schema : [])
const initialForm = computed(() => {
  const obj = {}
  for (const field of schema.value) {
    if (field?.key) obj[field.key] = ''
  }
  return obj
})
const isCreateUser = computed(() => props.team?.onboarding_behavior === 'create_user')
const form = useForm({ ...initialForm.value, terms_accepted: false })

function submit() {
  form.post(route('teams.join.store', props.team.slug), { preserveScroll: true })
}

const page = usePage()
const flashError = computed(() => page.props.flash?.error)
const flashSuccess = computed(() => page.props.flash?.success)
</script>

<template>
  <WebLayout>
    <main class="min-h-screen bg-zinc-900 px-4 py-12 text-white">
      <div class="mx-auto max-w-xl">
        <h1 class="text-2xl font-bold">
          {{ team.onboarding_title || `Inscreva-se | ${team.name}` }}
        </h1>
        <p v-if="flashError" class="mt-2 text-red-400">
          {{ flashError }}
        </p>
        <p v-if="flashSuccess" class="mt-2 text-green-400">
          {{ flashSuccess }}
        </p>
        <form class="mt-6 space-y-4" @submit.prevent="submit">
          <div v-for="field in schema" :key="field.key">
            <label :for="field.key" class="block text-sm">{{ field.label || field.key }}</label>
            <input
              :id="field.key"
              v-model="form[field.key]"
              type="text"
              class="mt-1 w-full rounded border border-zinc-600 bg-zinc-800 px-3 py-2 text-white"
              :required="!!field.required"
            />
            <p v-if="form.errors[field.key]" class="mt-1 text-sm text-red-400">
              {{ form.errors[field.key] }}
            </p>
          </div>
          <div v-if="isCreateUser" class="flex items-center gap-2">
            <input id="terms" v-model="form.terms_accepted" type="checkbox" required />
            <label for="terms">Aceito os termos e condições</label>
          </div>
          <p v-if="form.errors.terms_accepted" class="text-sm text-red-400">
            {{ form.errors.terms_accepted }}
          </p>
          <button
            type="submit"
            class="w-full rounded bg-indigo-600 py-2 font-medium hover:bg-indigo-700"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Enviando...' : 'Enviar' }}
          </button>
        </form>
      </div>
    </main>
  </WebLayout>
</template>
