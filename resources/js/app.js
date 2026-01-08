import { createInertiaApp, router } from '@inertiajs/vue3'
import { createHead } from '@unhead/vue'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { CapoPlugin } from 'unhead'
import { createApp, h } from 'vue'
import { ZiggyVue } from 'ziggy-js'
import './bootstrap'
import '../css/app.css'
import { refreshXsrfCookie, syncCsrfMetaFromCookie } from '@/utils/csrf.js'

/**
 * This is used from unhead plugin to use seo meta tags
 * @see {@link https://unhead.unjs.io/setup/unhead/introduction} For createHead instance
 * @see {@link https://unhead.unjs.io/plugins/plugins/capo} For CapoPlugin
 */
const head = createHead({
  plugins: [
    CapoPlugin(),
  ],
})

// Mantém o <meta name="csrf-token"> sincronizado com o cookie XSRF-TOKEN.
// Isso evita 419 após login via Inertia (token da sessão muda sem reload completo).
syncCsrfMetaFromCookie()
let lastAuthState = null
router.on('success', (event) => {
  const user = event.detail.page?.props?.auth?.user
  const isAuthed = !!user
  if (lastAuthState === null) {
    lastAuthState = isAuthed
    return
  }
  if (lastAuthState !== isAuthed) {
    lastAuthState = isAuthed
    refreshXsrfCookie().catch(() => {
      // se falhar, o próximo reload/GET vai sincronizar; não bloquear a navegação
    })
  }
})

createInertiaApp({
  resolve: name => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      .use(head)
      .mount(el)
  },
  progress: {
    color: '#4B5563',
  },
})
