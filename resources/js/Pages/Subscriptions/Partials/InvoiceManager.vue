<template>
  <section class="space-y-8 animate-in fade-in slide-in-from-bottom-8 duration-1000">
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-4 px-2">
      <div class="space-y-1">
        <h3 class="text-xl font-black text-slate-900 uppercase tracking-tighter italic">Histórico de Faturas</h3>
        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Dados processados de forma segura pelo Stripe</p>
      </div>
      <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400 bg-slate-100/50 px-4 py-2 rounded-xl border border-slate-200/50">
        <Icon icon="lucide:file-text" class="size-3.5" />
        {{ invoices.length }} Registros Encontrados
      </div>
    </header>

    <div class="relative group">
      <div class="absolute -inset-[1px] bg-gradient-to-r from-slate-200/50 via-slate-100/50 to-slate-200/50 rounded-[2.5rem] blur-sm opacity-50"></div>
      <div class="relative bg-white/90 backdrop-blur-xl rounded-[2.5rem] p-2 shadow-2xl shadow-slate-200/50 border border-white/80 overflow-hidden">
        <div class="overflow-x-auto">
          <Table>
            <TableHeader>
              <TableRow class="hover:bg-transparent border-slate-100">
                <TableHead class="text-[10px] font-black uppercase tracking-widest text-slate-400 h-14 px-6">Identificador</TableHead>
                <TableHead class="text-[10px] font-black uppercase tracking-widest text-slate-400 h-14">Data Emissão</TableHead>
                <TableHead class="text-[10px] font-black uppercase tracking-widest text-slate-400 h-14">Status</TableHead>
                <TableHead class="text-[10px] font-black uppercase tracking-widest text-slate-400 h-14 text-right">Valor Total</TableHead>
                <TableHead class="text-[10px] font-black uppercase tracking-widest text-slate-400 h-14 text-right px-6">Ações</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="invoice in invoices" :key="invoice.id" class="group/row hover:bg-slate-50/50 border-slate-50 transition-colors">
                <TableCell class="py-5 px-6">
                  <span class="text-[10px] font-black text-slate-900 uppercase tracking-tighter">{{ invoice.id }}</span>
                </TableCell>
                <TableCell class="py-5">
                  <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">{{ formatDate(invoice.created) }}</span>
                </TableCell>
                <TableCell class="py-5">
                  <div class="flex">
                    <span
                      :class="[
                        'inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border transition-all',
                        invoice.status === 'paid' 
                          ? 'bg-emerald-50 text-emerald-600 border-emerald-100/50 group-hover/row:bg-emerald-600 group-hover/row:text-white group-hover/row:border-emerald-600' 
                          : 'bg-slate-50 text-slate-500 border-slate-100 group-hover/row:bg-slate-900 group-hover/row:text-white group-hover/row:border-slate-900'
                      ]"
                    >
                      <Icon :icon="invoice.status === 'paid' ? 'lucide:check-circle-2' : 'lucide:clock'" class="size-3" />
                      {{ invoice.status === 'paid' ? 'Pago' : invoice.status }}
                    </span>
                  </div>
                </TableCell>
                <TableCell class="py-5 text-right font-black text-slate-900 text-[11px] italic">
                  {{ formatMoney(invoice.amount_paid, invoice.currency) }}
                </TableCell>
                <TableCell class="py-5 text-right px-6">
                  <Button
                    variant="ghost"
                    as="a"
                    :href="invoice.hosted_invoice_url"
                    target="_blank"
                    class="size-10 rounded-xl bg-slate-50 text-slate-400 border border-slate-100 hover:bg-slate-900 hover:text-white hover:border-slate-900 hover:scale-110 active:scale-90 transition-all cursor-pointer inline-flex items-center justify-center p-0"
                  >
                    <Icon icon="lucide:download" class="size-4" />
                  </Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>
    </div>
  </section>
</template>

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
