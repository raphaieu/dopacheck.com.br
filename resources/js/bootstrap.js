import axios from 'axios'

window.axios = axios

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Importante para CSRF no Laravel (especialmente em requests do Inertia/axios como /login):
// - Envia o token do <meta name="csrf-token"> via X-CSRF-TOKEN (não passa pelo decrypt do X-XSRF-TOKEN)
// - Garante cookies em requests same-origin quando necessário
window.axios.defaults.withCredentials = true

const token = document.head.querySelector('meta[name="csrf-token"]')
if (token) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content
}
