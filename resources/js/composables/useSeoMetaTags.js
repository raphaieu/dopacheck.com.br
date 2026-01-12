import { useSeoMeta } from '@unhead/vue'

const siteUrl = typeof window !== 'undefined'
  ? window.location.origin
  : 'https://dopacheck.com.br'

// Default SEO meta tags
const defaultSeoMeta = {
  title: 'DOPA Check',
  titleTemplate: '%s | DOPA Check',
  description: 'DOPA Check é o Strava dos hábitos e da mente. Um sistema de check-ins que transforma disciplina em algo visual, social e viciante - no bom sentido.',
  keywords: 'DOPA Check, hábitos, tracker de hábitos, desafios, check-in, rotina, produtividade, bem-estar, metas, streak, comunidade, dashboard, mobile-first, WhatsApp',
  robots: 'index, follow',
  themeColor: '#7b18ed',

  // Open Graph
  ogTitle: '%s | DOPA Check',
  ogDescription: 'DOPA Check é o Strava dos hábitos e da mente. Um sistema de check-ins que transforma disciplina em algo visual, social e viciante - no bom sentido.',
  ogUrl: siteUrl,
  ogType: 'website',
  ogImage: `${siteUrl}/images/og.webp`,
  ogSiteName: 'DOPA Check',
  ogLocale: 'pt_BR',

  // Twitter
  twitterTitle: '%s | DOPA Check',
  twitterDescription: 'DOPA Check é o Strava dos hábitos e da mente. Um sistema de check-ins que transforma disciplina em algo visual, social e viciante - no bom sentido.',
  twitterCard: 'summary_large_image',
  twitterImage: `${siteUrl}/images/og.webp`,
  twitterSite: '@raphaieu',
}

/**
 * Composable for managing SEO meta tags
 * @param {object|null} seoMeta - Custom SEO meta tags to apply
 * @param {object} options - Configuration options
 * @param {boolean} options.merge - When true, merges custom meta tags with defaults.
 *                                 When false, only uses custom meta tags.
 *                                 Useful for pages that need completely custom SEO
 *                                 without inheriting defaults.
 * @returns {void}
 */
export function useSeoMetaTags(seoMeta, options = { merge: true }) {
  if (!seoMeta)
    return useSeoMeta(defaultSeoMeta)

  return useSeoMeta(
    options.merge
      ? { ...defaultSeoMeta, ...seoMeta }
      : seoMeta,
  )
}
