import { cva, type VariantProps } from 'class-variance-authority'

export { default as Alert } from './Alert.vue'
export { default as AlertDescription } from './AlertDescription.vue'
export { default as AlertTitle } from './AlertTitle.vue'

export const alertVariants = cva(
  'relative w-full rounded-lg border px-4 py-3 text-sm grid has-[>svg]:grid-cols-[calc(var(--spacing)*4)_1fr] grid-cols-[0_1fr] has-[>svg]:gap-x-3 gap-y-0.5 items-start [&>svg]:size-4 [&>svg]:translate-y-0.5 [&>svg]:text-current',
  {
    variants: {
      variant: {
        default: 'bg-card text-card-foreground',
        success:
          'border-emerald-200 bg-emerald-50 text-emerald-950 *:data-[slot=alert-description]:text-emerald-900/90 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-50 dark:*:data-[slot=alert-description]:text-emerald-50/85',
        warning:
          'border-amber-200 bg-amber-50 text-amber-950 *:data-[slot=alert-description]:text-amber-900/90 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-50 dark:*:data-[slot=alert-description]:text-amber-50/85',
        destructive:
          'border-red-200 bg-red-50 text-red-950 *:data-[slot=alert-description]:text-red-900/90 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-50 dark:*:data-[slot=alert-description]:text-red-50/85',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
)

export type AlertVariants = VariantProps<typeof alertVariants>
