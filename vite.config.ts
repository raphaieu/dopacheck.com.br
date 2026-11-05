import path, { resolve } from 'node:path'
import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import { defineConfig } from 'vite'

const viteOrigin = process.env.VITE_ORIGIN || 'http://localhost:5173'
const viteUrl = new URL(viteOrigin)
const appOrigin = process.env.APP_URL || 'http://localhost:8010'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.js'],
      // Evita reload infinito ao "assistir" arquivos gerados em storage/.
      // Mantém refresh somente para fontes reais.
      refresh: [
        'resources/views/**',
        'routes/**',
        'app/**',
        'lang/**',
      ],
    }),
    tailwindcss(),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js'),
      'ziggy-js': resolve(__dirname, 'vendor/tightenco/ziggy'),
    },
  },
  server: {
    origin: viteOrigin,
    cors: {
      origin: appOrigin,
    },
    hmr: {
      host: viteUrl.hostname,
      clientPort: Number(viteUrl.port || (viteUrl.protocol === 'https:' ? 443 : 80)),
      protocol: viteUrl.protocol === 'https:' ? 'wss' : 'ws',
    },
    watch: {
      // Evita reload infinito no dev por arquivos gerados (views compiladas, tmp, cache, etc.)
      ignored: [
        '**/storage/**',
        '**/bootstrap/cache/**',
        '**/public/build/**',
        '**/vendor/**',
      ],
    },
  },
})
