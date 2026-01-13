<script setup>
import { computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/button/Button.vue'
import Alert from '@/components/ui/alert/Alert.vue'
import AlertTitle from '@/components/ui/alert/AlertTitle.vue'
import AlertDescription from '@/components/ui/alert/AlertDescription.vue'
import Table from '@/components/ui/table/Table.vue'
import TableHeader from '@/components/ui/table/TableHeader.vue'
import TableRow from '@/components/ui/table/TableRow.vue'
import TableHead from '@/components/ui/table/TableHead.vue'
import TableBody from '@/components/ui/table/TableBody.vue'
import TableCell from '@/components/ui/table/TableCell.vue'

const props = defineProps({
  team: Object,
  status: String,
  applications: Array,
  pagination: Object,
})

const page = usePage()
const flashSuccess = computed(() => page.props.flash?.success)

const statusTabs = [
  { key: 'pending', label: 'Pendentes' },
  { key: 'approved', label: 'Aprovadas' },
  { key: 'rejected', label: 'Rejeitadas' },
]

function setStatus(nextStatus) {
  router.visit(route('teams.applications.index', { team: props.team.id, status: nextStatus }), {
    preserveState: true,
    preserveScroll: true,
  })
}

function approve(app) {
  router.patch(route('teams.applications.update', { team: props.team.id, application: app.id }), { action: 'approve' }, {
    preserveScroll: true,
  })
}

function reject(app) {
  router.patch(route('teams.applications.update', { team: props.team.id, application: app.id }), { action: 'reject' }, {
    preserveScroll: true,
  })
}
</script>

<template>
  <AppLayout title="Inscrições do Team">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Inscrições do Team
          </h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ team?.name }} <span v-if="team?.slug">· /join/{{ team.slug }}</span>
          </p>
        </div>
        <Link v-if="team?.slug" :href="route('teams.join.show', team.slug)" class="text-sm text-blue-600 hover:underline">
          Abrir link público
        </Link>
      </div>
    </template>

    <div class="max-w-7xl">
      <Alert v-if="flashSuccess" class="mb-6" variant="default">
        <AlertTitle>Ok</AlertTitle>
        <AlertDescription>{{ flashSuccess }}</AlertDescription>
      </Alert>

      <div class="mb-4 flex gap-2">
        <Button
          v-for="tab in statusTabs"
          :key="tab.key"
          :variant="tab.key === status ? 'default' : 'secondary'"
          size="sm"
          type="button"
          @click="setStatus(tab.key)"
        >
          {{ tab.label }}
        </Button>
      </div>

      <div class="rounded-xl border bg-white dark:bg-gray-900">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Nome</TableHead>
              <TableHead>Email</TableHead>
              <TableHead>WhatsApp</TableHead>
              <TableHead>Cidade</TableHead>
              <TableHead>Bairro</TableHead>
              <TableHead>Circle</TableHead>
              <TableHead class="text-right">Ações</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="app in applications" :key="app.id">
              <TableCell class="font-medium">
                <div class="flex flex-col">
                  <span>{{ app.name }}</span>
                  <span class="text-xs text-gray-500">{{ app.birthdate }}</span>
                </div>
              </TableCell>
              <TableCell>{{ app.email }}</TableCell>
              <TableCell>{{ app.whatsapp_number }}</TableCell>
              <TableCell>{{ app.city }}</TableCell>
              <TableCell>{{ app.neighborhood }}</TableCell>
              <TableCell>
                <a :href="app.circle_url" target="_blank" rel="noreferrer" class="text-blue-600 hover:underline">
                  abrir
                </a>
              </TableCell>
              <TableCell class="text-right">
                <div v-if="status === 'pending'" class="flex justify-end gap-2">
                  <Button size="sm" type="button" @click="approve(app)">
                    Aprovar
                  </Button>
                  <Button variant="secondary" size="sm" type="button" @click="reject(app)">
                    Rejeitar
                  </Button>
                </div>
                <span v-else class="text-xs text-gray-500">
                  {{ app.status }}
                </span>
              </TableCell>
            </TableRow>
            <TableRow v-if="!applications?.length">
              <TableCell colspan="7" class="py-10 text-center text-sm text-gray-500">
                Nenhuma inscrição encontrada.
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <p class="mt-4 text-xs text-gray-500">
        Total: {{ pagination?.total ?? applications?.length ?? 0 }}
      </p>
    </div>
  </AppLayout>
</template>

