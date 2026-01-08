function getCookie(name) {
  // document.cookie: "a=b; c=d"
  const value = `; ${document.cookie}`
  const parts = value.split(`; ${name}=`)
  if (parts.length !== 2) return null
  return parts.pop()?.split(';').shift() ?? null
}

export function getXsrfToken() {
  const raw = getCookie('XSRF-TOKEN')
  if (!raw) return null
  try {
    return decodeURIComponent(raw)
  } catch {
    return raw
  }
}

export function getCsrfTokenFromMeta() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || null
}

export function syncCsrfMetaFromCookie() {
  const token = getXsrfToken()
  if (!token) return false
  const meta = document.querySelector('meta[name="csrf-token"]')
  if (!meta) return false
  meta.setAttribute('content', token)
  return true
}

export async function refreshXsrfCookie() {
  // Endpoint leve que garante um GET "real" no backend e atualiza o cookie XSRF-TOKEN
  await fetch('/csrf-cookie', {
    method: 'GET',
    credentials: 'same-origin',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    cache: 'no-store',
  })
  syncCsrfMetaFromCookie()
}

export function csrfHeaders(extra = {}) {
  const xsrf = getXsrfToken()
  const meta = getCsrfTokenFromMeta()

  // Preferir cookie (atualiza com a sessão pós-login). Fallback para meta.
  const token = xsrf || meta || ''

  return {
    'X-Requested-With': 'XMLHttpRequest',
    // Laravel aceita ambos. `X-XSRF-TOKEN` é o padrão (cookie XSRF-TOKEN).
    // Mantemos `X-CSRF-TOKEN` por compatibilidade com código legado.
    'X-XSRF-TOKEN': token,
    'X-CSRF-TOKEN': token,
    ...extra,
  }
}

export async function csrfFetch(url, options = {}, { retryOn419 = true } = {}) {
  const mergedHeaders = csrfHeaders(options.headers || {})

  const response = await fetch(url, {
    ...options,
    credentials: 'same-origin',
    headers: mergedHeaders,
    cache: options.cache ?? 'no-store',
  })

  if (retryOn419 && response.status === 419) {
    // Token desatualizado (muito comum após login via Inertia sem reload)
    try {
      await refreshXsrfCookie()
    } catch {
      // ignore
    }
    return fetch(url, {
      ...options,
      credentials: 'same-origin',
      headers: csrfHeaders(options.headers || {}),
      cache: options.cache ?? 'no-store',
    })
  }

  return response
}


