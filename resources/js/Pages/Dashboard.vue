<script setup>
import StatsCard from '@/components/StatsCard.vue'
import AppLayout from '@/layouts/AppLayout.vue'

const stats = [
  {
    value: 'Documentação do Projeto',
    description: 'Entenda o produto e como rodar localmente',
    link: 'https://github.com/raphaieu/dopacheck.com.br#como-funciona',
    icon: 'lucide:book-open',
  },
  {
    value: 'Repositório no GitHub',
    description: 'Acompanhe o desenvolvimento e contribua',
    link: 'https://github.com/raphaieu/dopacheck.com.br',
    icon: 'lucide:github',
  },
  {
    value: 'Roadmap',
    description: 'Veja o que vem por aí',
    link: 'https://github.com/raphaieu/dopacheck.com.br/issues',
    icon: 'lucide:map',
  },
  {
    value: 'Comunidade',
    description: 'Participe e acompanhe novidades',
    link: 'https://t.me/reservatoriodedopamina',
    icon: 'lucide:users',
  },
]
</script>

<template>
  <AppLayout title="Dashboard">
    <template #header>
      <h2 class="text-xl font-semibold leading-tight">
        Dashboard
      </h2>
    </template>

    <div>
      <div class="flex flex-1 flex-col gap-4 pt-0">
        <div class="grid auto-rows-min gap-4 md:grid-cols-2">
          <StatsCard
            v-for="stat in stats"
            :key="stat.title"
            v-bind="stat"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
