<script setup>
import { Head, Link } from '@inertiajs/vue3'

defineProps({
  status: { type: Number, default: 404 },
  message: { type: String, default: 'Página não encontrada' },
})
</script>

<template>
  <Head title="Página não encontrada" />

  <main class="min-h-screen bg-slate-950 text-slate-100">
    <div class="mx-auto flex max-w-3xl flex-col gap-6 px-6 py-16">
      <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-8">
        <p class="text-sm font-medium text-slate-400">
          Erro {{ status }}
        </p>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight">
          {{ message }}
        </h1>
        <p class="mt-3 text-slate-300">
          O endereço que você tentou acessar não existe ou foi movido.
        </p>

        <div class="mt-6 flex flex-wrap gap-3">
          <Link
            href="/"
            class="inline-flex items-center justify-center rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-400"
          >
            Voltar para o início
          </Link>
          <a
            href="javascript:history.back()"
            class="inline-flex items-center justify-center rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-100 hover:bg-slate-800"
          >
            Voltar
          </a>
        </div>
      </div>
    </div>
  </main>
</template>


