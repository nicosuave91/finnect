<template>
  <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
    <div class="px-4 sm:px-6 lg:px-8">
      <div class="flex h-16 justify-between items-center">
        <!-- Left side -->
        <div class="flex items-center">
          <!-- Mobile menu button -->
          <button
            type="button"
            class="lg:hidden -m-2.5 p-2.5 text-gray-700 dark:text-gray-300"
            @click="$emit('toggle-sidebar')"

            aria-label="Open sidebar"

          >
            <Icon name="heroicons:bars-3" class="h-6 w-6" />
          </button>

          <!-- Breadcrumb -->
          <nav class="hidden lg:flex" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-4">
              <li v-for="(item, index) in breadcrumbs" :key="item.name" class="flex">
                <div class="flex items-center">
                  <Icon
                    v-if="index > 0"
                    name="heroicons:chevron-right"
                    class="h-5 w-5 text-gray-400 mr-4"
                  />
                  <NuxtLink
                    v-if="item.href"
                    :to="item.href"
                    class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                  >
                    {{ item.name }}
                  </NuxtLink>
                  <span
                    v-else
                    class="text-sm font-medium text-gray-900 dark:text-white"
                  >
                    {{ item.name }}
                  </span>
                </div>
              </li>
            </ol>
          </nav>
        </div>

        <!-- Right side -->
        <div class="flex items-center space-x-4">
          <!-- Search -->
          <div class="hidden md:block">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <Icon name="heroicons:magnifying-glass" class="h-5 w-5 text-gray-400" />
              </div>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search loans, borrowers..."

                aria-label="Search"

                class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                @keydown.enter="handleSearch"
              />
            </div>
          </div>

          <!-- Notifications -->
          <button
            type="button"
            class="relative p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
            @click="showNotifications = !showNotifications"

            aria-label="Notifications"
            :aria-expanded="showNotifications"
            aria-controls="notification-panel"

          >
            <Icon name="heroicons:bell" class="h-6 w-6" />
            <span
              v-if="unreadNotifications > 0"
              class="absolute -top-1 -right-1 h-5 w-5 bg-error-500 text-white text-xs rounded-full flex items-center justify-center"
            >
              {{ unreadNotifications }}
            </span>
          </button>

          <!-- Tenant switcher -->
          <Menu v-if="tenantStore.isMultiTenant" as="div" class="relative">

            <MenuButton
              class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
              aria-label="Tenant menu"
            >

            <MenuButton class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">

              <div class="h-8 w-8 bg-primary-100 dark:bg-primary-900 rounded-lg flex items-center justify-center mr-2">
                <Icon name="heroicons:building-office" class="h-5 w-5 text-primary-600 dark:text-primary-400" />
              </div>
              {{ tenantStore.tenantName }}
              <Icon name="heroicons:chevron-down" class="ml-1 h-4 w-4" />
            </MenuButton>
            <transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <MenuItems class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white dark:bg-gray-800 py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                <MenuItem
                  v-for="tenant in tenantStore.tenants"
                  :key="tenant.id"
                  @click="switchTenant(tenant.id)"
                >
                  <button
                    :class="[
                      'w-full text-left px-4 py-2 text-sm',
                      tenant.id === tenantStore.currentTenant?.id
                        ? 'bg-primary-50 dark:bg-primary-900 text-primary-700 dark:text-primary-300'
                        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                    ]"
                  >
                    <div class="flex items-center">
                      <div class="h-6 w-6 bg-primary-100 dark:bg-primary-900 rounded flex items-center justify-center mr-3">
                        <Icon name="heroicons:building-office" class="h-4 w-4 text-primary-600 dark:text-primary-400" />
                      </div>
                      <div>
                        <div class="font-medium">{{ tenant.name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ tenant.domain }}</div>
                      </div>
                    </div>
                  </button>
                </MenuItem>
              </MenuItems>
            </transition>
          </Menu>

          <!-- Theme toggle -->
          <button
            type="button"
            class="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
            @click="toggleTheme"
          >
            <Icon
              :name="colorMode.value === 'dark' ? 'heroicons:sun' : 'heroicons:moon'"
              class="h-6 w-6"
            />
          </button>

          <!-- User menu -->
          <Menu as="div" class="relative">

            <MenuButton
              class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
              aria-label="User menu"
            >

            <MenuButton class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
              <div class="h-8 w-8 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center mr-2">
                <span class="text-sm font-medium text-primary-600 dark:text-primary-400">
                  {{ authStore.userInitials }}
                </span>
              </div>
              <span class="hidden md:block">{{ authStore.userFullName }}</span>
              <Icon name="heroicons:chevron-down" class="ml-1 h-4 w-4" />
            </MenuButton>
            <transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <MenuItems class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white dark:bg-gray-800 py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                <MenuItem>
                  <NuxtLink
                    to="/profile"
                    class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                  >
                    Your Profile
                  </NuxtLink>
                </MenuItem>
                <MenuItem>
                  <NuxtLink
                    to="/settings"
                    class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                  >
                    Settings
                  </NuxtLink>
                </MenuItem>
                <MenuItem>
                  <button
                    @click="handleLogout"
                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                  >
                    Sign out
                  </button>
                </MenuItem>
              </MenuItems>
            </transition>
          </Menu>
        </div>
      </div>
    </div>

    <!-- Notifications dropdown -->
    <div
      v-if="showNotifications"
      class="absolute right-4 top-16 z-50 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700"
      id="notification-panel"
      role="region"
      aria-label="Notifications"

    >
      <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Notifications</h3>
      </div>
      <div class="max-h-96 overflow-y-auto">
        <div v-if="notifications.length === 0" class="p-4 text-center text-gray-500 dark:text-gray-400">
          No notifications
        </div>
        <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
          <div
            v-for="notification in notifications"
            :key="notification.id"
            class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            <div class="flex items-start">
              <div class="flex-shrink-0">
                <Icon
                  :name="getNotificationIcon(notification.type)"
                  :class="[
                    'h-5 w-5',
                    notification.type === 'error' ? 'text-error-500' : 'text-primary-500'
                  ]"
                />
              </div>
              <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ notification.title }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  {{ notification.message }}
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                  {{ formatTime(notification.created_at) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'

// Emits
defineEmits<{
  'toggle-sidebar': []
}>()

// Stores
const authStore = useAuthStore()
const tenantStore = useTenantStore()

// State
const searchQuery = ref('')
const showNotifications = ref(false)
const notifications = ref([])
const unreadNotifications = computed(() => notifications.value.filter(n => !n.is_read).length)

// Computed
const colorMode = useColorMode()

const breadcrumbs = computed(() => {
  const route = useRoute()
  const pathSegments = route.path.split('/').filter(Boolean)
  const breadcrumbs = [{ name: 'Dashboard', href: '/' }]

  let currentPath = ''
  pathSegments.forEach((segment, index) => {
    currentPath += `/${segment}`
    const isLast = index === pathSegments.length - 1
    
    breadcrumbs.push({
      name: segment.charAt(0).toUpperCase() + segment.slice(1).replace('-', ' '),
      href: isLast ? undefined : currentPath
    })
  })

  return breadcrumbs
})

// Methods
const handleSearch = () => {
  if (searchQuery.value.trim()) {
    navigateTo(`/search?q=${encodeURIComponent(searchQuery.value)}`)
  }
}

const switchTenant = async (tenantId: number) => {
  await tenantStore.switchTenant(tenantId)
}

const toggleTheme = () => {
  colorMode.preference = colorMode.value === 'dark' ? 'light' : 'dark'
}

const handleLogout = async () => {
  await authStore.logout()
}

const getNotificationIcon = (type: string) => {
  const icons = {
    info: 'heroicons:information-circle',
    success: 'heroicons:check-circle',
    warning: 'heroicons:exclamation-triangle',
    error: 'heroicons:x-circle'
  }
  return icons[type] || 'heroicons:bell'
}

const formatTime = (timestamp: string) => {
  return new Date(timestamp).toLocaleString()
}

// Close notifications when clicking outside
onMounted(() => {
  document.addEventListener('click', (event) => {
    const target = event.target as HTMLElement
    if (!target.closest('[data-notifications]')) {
      showNotifications.value = false
    }
  })
})
</script>