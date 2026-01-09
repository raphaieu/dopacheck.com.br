import axios from 'axios'
import { getXsrfToken, getCsrfTokenFromMeta, refreshXsrfCookie, syncCsrfMetaFromCookie } from './utils/csrf.js'

window.axios = axios

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Importante para CSRF no Laravel (especialmente em requests do Inertia/axios como /login):
// - Envia o token do <meta name="csrf-token"> via X-CSRF-TOKEN (não passa pelo decrypt do X-XSRF-TOKEN)
// - Garante cookies em requests same-origin quando necessário
window.axios.defaults.withCredentials = true

// Mantém o meta token alinhado com o cookie (evita token antigo após mudanças de sessão sem reload).
syncCsrfMetaFromCookie()

// IMPORTANTÍSSIMO:
// Não setar X-CSRF-TOKEN "fixo" em defaults, pois o token pode mudar (ex.: login via Inertia),
// e o Laravel valida X-CSRF-TOKEN antes de X-XSRF-TOKEN.
// Em vez disso, injetamos o token atualizado em cada request.
window.axios.interceptors.request.use((config) => {
  const token = getXsrfToken() || getCsrfTokenFromMeta()
  if (token) {
    config.headers = config.headers || {}
    config.headers['X-CSRF-TOKEN'] = token
  }
  return config
})

// Retry único em 419: renova XSRF cookie e reenvia.
window.axios.interceptors.response.use(
  (response) => response,
  async (error) => {
    const status = error?.response?.status
    const original = error?.config

    if (status === 419 && original && !original.__retried419) {
      original.__retried419 = true
      try {
        await refreshXsrfCookie()
      } catch {
        // ignore
      }
      return window.axios(original)
    }

    return Promise.reject(error)
  },
)
