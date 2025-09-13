export default defineNuxtConfig({
  devtools: { enabled: true },
  
  // TypeScript configuration
  typescript: {
    strict: true,
    typeCheck: true
  },

  // CSS framework
  css: ['~/assets/css/main.css'],

  // Modules
  modules: [
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt',
    '@vueuse/nuxt',
    '@nuxtjs/color-mode',
    '@nuxtjs/google-fonts',
    '@nuxtjs/robots',
    '@nuxtjs/seo'
  ],

  // Runtime config
  runtimeConfig: {
    public: {
      apiBaseUrl: process.env.NUXT_PUBLIC_API_BASE_URL || 'http://localhost:8000/api',
      appName: 'Finnect',
      appVersion: '1.0.0',
      environment: process.env.NODE_ENV || 'development'
    }
  },

  // App configuration
  app: {
    head: {
      title: 'Finnect - Mortgage Broker-Dealer Platform',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'Comprehensive mortgage broker-dealer platform for loan management and compliance' },
        { name: 'theme-color', content: '#1e40af' }
      ],
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
        { rel: 'apple-touch-icon', href: '/apple-touch-icon.png' }
      ]
    }
  },

  // PWA configuration
  pwa: {
    registerType: 'autoUpdate',
    workbox: {
      navigateFallback: '/',
      globPatterns: ['**/*.{js,css,html,png,svg,ico}']
    },
    client: {
      installPrompt: true
    },
    devOptions: {
      enabled: true,
      suppressWarnings: true
    }
  },

  // SEO configuration
  site: {
    url: process.env.NUXT_PUBLIC_SITE_URL || 'https://finnect.com',
    name: 'Finnect',
    description: 'Comprehensive mortgage broker-dealer platform',
    defaultLocale: 'en'
  },

  // Google Fonts
  googleFonts: {
    families: {
      Inter: [300, 400, 500, 600, 700],
      'Source Sans Pro': [300, 400, 500, 600, 700]
    },
    display: 'swap'
  },

  // Color mode
  colorMode: {
    preference: 'system',
    fallback: 'light',
    hid: 'nuxt-color-mode-script',
    globalName: '__NUXT_COLOR_MODE__',
    componentName: 'ColorScheme',
    classPrefix: '',
    classSuffix: '',
    storageKey: 'nuxt-color-mode'
  },

  // Tailwind CSS
  tailwindcss: {
    configPath: '~/tailwind.config.js',
    exposeConfig: true
  },

  // Build configuration
  build: {
    transpile: ['naive-ui', 'vueuc', '@css-render/vue3-ssr', '@juggle/resize-observer']
  },

  // Vite configuration
  vite: {
    define: {
      'process.env.DEBUG': false
    },
    optimizeDeps: {
      include: [
        'naive-ui',
        'vueuc',
        '@css-render/vue3-ssr',
        '@juggle/resize-observer'
      ]
    }
  },

  // Nitro configuration
  nitro: {
    experimental: {
      wasm: true
    }
  },

  // Router configuration
  router: {
    middleware: ['auth', 'tenant']
  },

  // Server-side rendering
  ssr: true,

  // Experimental features
  experimental: {
    payloadExtraction: false,
    inlineSSRStyles: false
  }
})