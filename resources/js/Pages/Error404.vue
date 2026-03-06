<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import Button from '@/components/ui/button/Button.vue'
import { Card, CardContent } from '@/components/ui/card'

defineProps({
  status: { type: Number, default: 404 },
  message: { type: String, default: 'Página não encontrada' },
})
</script>

<template>
  <Head title="404 - Página não encontrada" />

  <main class="relative min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-purple-50 overflow-hidden p-6 text-slate-900">
    <!-- Decorative Blurs -->
    <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-blue-400/10 rounded-full blur-[120px] animate-pulse pointer-events-none"></div>
    <div class="absolute top-[20%] -right-[10%] w-[35%] h-[35%] bg-purple-400/10 rounded-full blur-[120px] animate-pulse pointer-events-none" style="animation-delay: 2s"></div>
    <div class="absolute -bottom-[10%] left-[20%] w-[30%] h-[30%] bg-emerald-400/10 rounded-full blur-[120px] animate-pulse pointer-events-none" style="animation-delay: 4s"></div>

    <Card class="relative w-full max-w-[500px] shadow-2xl shadow-slate-200/50 border border-white/80 bg-white/70 backdrop-blur-xl rounded-[3rem] p-4 transition-all duration-500 z-10">
      <CardContent class="flex flex-col items-center text-center py-12 px-8">
        <!-- Icon/Badge -->
        <div class="size-20 rounded-3xl bg-gradient-to-br from-blue-600 via-violet-600 to-purple-600 flex items-center justify-center shadow-2xl shadow-blue-600/20 mb-10 transform -rotate-6 transition-transform hover:rotate-0">
          <Icon icon="lucide:compass" class="size-10 text-white" />
        </div>

        <!-- 404 Header -->
        <h1 class="text-[100px] font-black leading-none tracking-tighter bg-gradient-to-br from-blue-600 via-violet-600 to-purple-600 bg-clip-text text-transparent drop-shadow-sm select-none">
          404
        </h1>

        <!-- Content -->
        <div class="mt-4 space-y-4">
          <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tighter italic">Página não encontrada</h2>
          <p class="text-sm font-black uppercase tracking-widest text-slate-500 leading-relaxed max-w-[280px]">
            Parece que você está perdido no labirinto da procrastinação...
          </p>
        </div>

        <!-- Action Section -->
        <div class="mt-12 w-full flex flex-col gap-4">
          <Button
            :as="Link"
            href="/"
            class="h-16 w-full rounded-2xl bg-slate-900 text-white font-black uppercase tracking-[0.2em] text-[11px] shadow-xl shadow-slate-900/10 hover:shadow-2xl transition-all active:scale-95"
          >
            Voltar para a Home
            <Icon icon="lucide:home" class="ml-2 size-4" />
          </Button>

          <Link href="/sobre" class="text-[10px] font-black text-slate-400 hover:text-blue-600 uppercase tracking-[0.3em] transition-colors">
            Conhecer o Manifesto
          </Link>
        </div>
      </CardContent>

      <!-- Glass overlay detail -->
      <div class="absolute inset-0 rounded-[3rem] border border-white/10 pointer-events-none"></div>
    </Card>

    <!-- Footer Credit -->
    <p class="absolute bottom-8 text-[10px] font-black text-slate-400 uppercase tracking-[0.4em] select-none">
       DOPA Check · Built for consistency.
    </p>
  </main>
</template>


