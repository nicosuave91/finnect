import type { ThemeConfiguration, BrandingConfiguration } from '~/types'

export const useTheme = () => {
  const tenantStore = useTenantStore()
  const colorMode = useColorMode()

  // Get theme configuration
  const themeConfig = computed(() => tenantStore.getThemeConfiguration())
  const brandingConfig = computed(() => tenantStore.getBrandingConfiguration())

  // Apply theme to document
  const applyTheme = () => {
    if (process.client) {
      const root = document.documentElement
      const config = themeConfig.value

      // Set CSS custom properties
      root.style.setProperty('--color-primary', config.primary_color)
      root.style.setProperty('--color-secondary', config.secondary_color)

      // Update favicon
      if (config.favicon_url) {
        const favicon = document.querySelector('link[rel="icon"]') as HTMLLinkElement
        if (favicon) {
          favicon.href = config.favicon_url
        }
      }

      // Update logo
      const logoElements = document.querySelectorAll('[data-logo]')
      logoElements.forEach((element) => {
        if (config.logo_url) {
          element.setAttribute('src', config.logo_url)
        }
      })

      // Apply custom CSS
      if (config.custom_css) {
        let styleElement = document.getElementById('tenant-custom-css')
        if (!styleElement) {
          styleElement = document.createElement('style')
          styleElement.id = 'tenant-custom-css'
          document.head.appendChild(styleElement)
        }
        styleElement.textContent = config.custom_css
      }
    }
  }

  // Get computed theme colors
  const primaryColor = computed(() => themeConfig.value.primary_color)
  const secondaryColor = computed(() => themeConfig.value.secondary_color)
  const logoUrl = computed(() => themeConfig.value.logo_url)
  const faviconUrl = computed(() => themeConfig.value.favicon_url)

  // Get branding information
  const companyName = computed(() => brandingConfig.value.company_name)
  const tagline = computed(() => brandingConfig.value.tagline)
  const supportEmail = computed(() => brandingConfig.value.support_email)
  const supportPhone = computed(() => brandingConfig.value.support_phone)
  const websiteUrl = computed(() => brandingConfig.value.website_url)
  const socialMedia = computed(() => brandingConfig.value.social_media || {})

  // Generate color palette from primary color
  const colorPalette = computed(() => {
    const primary = primaryColor.value
    if (!primary) return {}

    // Convert hex to RGB
    const hex = primary.replace('#', '')
    const r = parseInt(hex.substr(0, 2), 16)
    const g = parseInt(hex.substr(2, 2), 16)
    const b = parseInt(hex.substr(4, 2), 16)

    // Generate color variations
    return {
      50: `rgb(${Math.min(255, r + 40)}, ${Math.min(255, g + 40)}, ${Math.min(255, b + 40)})`,
      100: `rgb(${Math.min(255, r + 30)}, ${Math.min(255, g + 30)}, ${Math.min(255, b + 30)})`,
      200: `rgb(${Math.min(255, r + 20)}, ${Math.min(255, g + 20)}, ${Math.min(255, b + 20)})`,
      300: `rgb(${Math.min(255, r + 10)}, ${Math.min(255, g + 10)}, ${Math.min(255, b + 10)})`,
      400: `rgb(${r}, ${g}, ${b})`,
      500: `rgb(${Math.max(0, r - 10)}, ${Math.max(0, g - 10)}, ${Math.max(0, b - 10)})`,
      600: `rgb(${Math.max(0, r - 20)}, ${Math.max(0, g - 20)}, ${Math.max(0, b - 20)})`,
      700: `rgb(${Math.max(0, r - 30)}, ${Math.max(0, g - 30)}, ${Math.max(0, b - 30)})`,
      800: `rgb(${Math.max(0, r - 40)}, ${Math.max(0, g - 40)}, ${Math.max(0, b - 40)})`,
      900: `rgb(${Math.max(0, r - 50)}, ${Math.max(0, g - 50)}, ${Math.max(0, b - 50)})`
    }
  })

  // Get theme-aware CSS classes
  const getThemeClasses = (baseClasses: string) => {
    const config = themeConfig.value
    if (!config.primary_color) return baseClasses

    // Replace default primary color classes with custom ones
    return baseClasses
      .replace(/bg-primary-\d+/g, `bg-[${config.primary_color}]`)
      .replace(/text-primary-\d+/g, `text-[${config.primary_color}]`)
      .replace(/border-primary-\d+/g, `border-[${config.primary_color}]`)
      .replace(/ring-primary-\d+/g, `ring-[${config.primary_color}]`)
  }

  // Update page title with branding
  const updatePageTitle = (title: string) => {
    const fullTitle = title ? `${title} - ${companyName.value}` : companyName.value
    useHead({ title: fullTitle })
  }

  // Get social media links
  const getSocialMediaLinks = () => {
    const social = socialMedia.value
    return Object.entries(social).map(([platform, url]) => ({
      platform,
      url,
      icon: getSocialMediaIcon(platform)
    }))
  }

  // Get social media icon
  const getSocialMediaIcon = (platform: string) => {
    const icons: Record<string, string> = {
      facebook: 'heroicons:facebook',
      twitter: 'heroicons:twitter',
      linkedin: 'heroicons:linkedin',
      instagram: 'heroicons:instagram',
      youtube: 'heroicons:youtube',
      github: 'heroicons:github'
    }
    return icons[platform] || 'heroicons:link'
  }

  // Initialize theme
  const initializeTheme = () => {
    applyTheme()
  }

  // Watch for theme changes
  watch(themeConfig, () => {
    applyTheme()
  }, { deep: true })

  // Watch for color mode changes
  watch(colorMode, () => {
    applyTheme()
  })

  // Watch for tenant changes to reapply theme
  watch(() => tenantStore.currentTenant, () => {
    applyTheme()
  })

  return {
    // Theme configuration
    themeConfig,
    brandingConfig,
    
    // Theme values
    primaryColor,
    secondaryColor,
    logoUrl,
    faviconUrl,
    
    // Branding values
    companyName,
    tagline,
    supportEmail,
    supportPhone,
    websiteUrl,
    socialMedia,
    
    // Computed values
    colorPalette,
    
    // Methods
    applyTheme,
    getThemeClasses,
    updatePageTitle,
    getSocialMediaLinks,
    getSocialMediaIcon,
    initializeTheme
  }
}