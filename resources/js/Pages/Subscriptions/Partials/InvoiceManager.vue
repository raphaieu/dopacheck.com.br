<script setup>
import { Icon } from '@iconify/vue'
import Button from '@/components/ui/button/Button.vue'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

defineProps({
  invoices: {
    type: Array,
    default: () => [],
  },
})

const formatDate = (unixSeconds) => {
  if (!unixSeconds) return ''
  return new Date(unixSeconds * 1000).toLocaleDateString('pt-BR')
}

const formatMoney = (amountInCents, currency = 'brl') => {
  const amount = Number(amountInCents || 0) / 100
  const upper = String(currency || 'brl').toUpperCase()
  const ccy = upper === 'BRL' ? 'BRL' : upper

  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: ccy,
  }).format(amount)
}
</script>

<template>
  <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
    <header class="mb-4">
      <h3 class="text-lg font-bold text-gray-900">
        Faturas
      </h3>
      <p class="text-sm text-gray-600">
        Veja e baixe suas faturas anteriores no Stripe.
      </p>
    </header>

    <div class="border border-gray-100 rounded-xl overflow-hidden">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Fatura</TableHead>
            <TableHead>Data</TableHead>
            <TableHead>Cliente</TableHead>
            <TableHead>Status</TableHead>
            <TableHead class="text-right">
              Valor
            </TableHead>
            <TableHead class="text-right">
              Baixar
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="invoice in invoices" :key="invoice.id">
            <TableCell class="font-medium">
              {{ invoice.id }}
            </TableCell>
            <TableCell>{{ formatDate(invoice.created) }}</TableCell>
            <TableCell>{{ invoice.customer_name }}</TableCell>
            <TableCell>
              <span
                :class="[
                  'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                  invoice.status === 'paid' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-700'
                ]"
              >
                {{ invoice.status }}
              </span>
            </TableCell>
            <TableCell class="text-right">
              {{ formatMoney(invoice.amount_paid, invoice.currency) }}
            </TableCell>
            <TableCell class="text-right">
              <Button
                variant="ghost"
                as="a"
                :href="invoice.hosted_invoice_url"
                target="_blank"
                class="hover:bg-purple-50"
              >
                <Icon icon="lucide:download" />
              </Button>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
  </section>
</template>
