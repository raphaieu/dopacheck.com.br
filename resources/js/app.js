import { createInertiaApp, router } from '@inertiajs/vue3'
import { createHead } from '@unhead/vue'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { CapoPlugin } from 'unhead'
import { createApp, h, ref } from 'vue'
import { ZiggyVue } from 'ziggy-js'
import { toast } from 'vue-sonner'
import Sonner from '@/components/ui/sonner/Sonner.vue'
import FullPageLoader from '@/components/FullPageLoader.vue'
import './bootstrap'
import '../css/app.css'
import 'vue-sonner/style.css'
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

// Flash messages (Inertia) -> toasts globais
let lastFlashSignature = null
function handleFlash(page) {
  const flash = page?.props?.flash || {}
  const message = flash.error || flash.success || flash.message
  if (!message) return

  const type = flash.error ? 'error' : (flash.success ? 'success' : 'message')
  const signature = `${type}:${message}`
  if (signature === lastFlashSignature) return
  lastFlashSignature = signature

  if (flash.error) toast.error(message)
  else if (flash.success) toast.success(message)
  else toast(message)
}

// Google Analytics (GA4) - Pageviews para SPA (Inertia)
function trackPageView(urlString) {
  if (typeof window === 'undefined' || typeof window.gtag !== 'function') return
  const id = import.meta.env.VITE_GA_MEASUREMENT_ID
  if (!id) return

  const url = new URL(urlString, window.location.origin)
  window.gtag('event', 'page_view', {
    page_title: document.title,
    page_location: url.href,
    page_path: `${url.pathname}${url.search}`,
    send_to: id,
  })
}

// dispara no primeiro load
trackPageView(window.location.href)
// dispara a cada navegação Inertia
router.on('navigate', (event) => {
  const nextUrl = event.detail?.page?.url
  if (!nextUrl) return
  trackPageView(nextUrl)
})

// Loader fullscreen para navegações Inertia (além do progress bar do topo)
const isNavigating = ref(false)
router.on('start', () => {
  isNavigating.value = true
})
router.on('finish', () => {
  isNavigating.value = false
})
router.on('error', () => {
  isNavigating.value = false
})
router.on('exception', () => {
  isNavigating.value = false
})
router.on('invalid', () => {
  isNavigating.value = false
})

createInertiaApp({
  resolve: name => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    // Monta o Sonner uma única vez para todo o app (evita ter que colocar em cada página/layout).
    const Root = {
      render: () => [
        h(FullPageLoader, { show: isNavigating.value }),
        h(Sonner, { position: 'top-center' }),
        h(App, props),
      ],
    }

    // dispara toast no primeiro render (SSR/hydration)
    handleFlash(props?.initialPage)
    // dispara a cada navegação/response
    router.on('success', (event) => handleFlash(event.detail?.page))

    return createApp(Root)
      .use(plugin)
      .use(ZiggyVue)
      .use(head)
      .mount(el)
  },
  progress: {
    color: '#4B5563',
  },
})
