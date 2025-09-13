<template>
  <div>
    <!-- Mobile sidebar overlay -->
    <div
      v-if="isOpen"
      class="fixed inset-0 z-40 lg:hidden"
      @click="$emit('close')"
    >
      <div class="absolute inset-0 bg-gray-600 opacity-75"></div>
    </div>

    <!-- Sidebar -->
    <div
      :class="[
        'sidebar',
        isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      ]"
    >
      <div class="flex h-full flex-col">
        <!-- Logo -->
        <div class="flex h-16 flex-shrink-0 items-center px-6 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center">
            <div class="h-8 w-8 bg-primary-600 rounded-lg flex items-center justify-center">
              <Icon name="heroicons:home-modern" class="h-6 w-6 text-white" />
            </div>
            <div class="ml-3">
              <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ tenantName }}
              </h1>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                Finnect
              </p>
            </div>
          </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
          <template v-for="item in navigation" :key="item.name">
            <!-- Single item -->
            <NuxtLink
              v-if="!item.children"
              :to="item.href"
              :class="[
                'nav-link',
                isActive(item.href) ? 'nav-link-active' : 'nav-link-inactive'
              ]"
            >
              <Icon :name="item.icon" class="h-5 w-5 mr-3" />
              {{ item.name }}
              <span v-if="item.badge" class="ml-auto status-badge status-error">
                {{ item.badge }}
              </span>
            </NuxtLink>

            <!-- Dropdown item -->
            <div v-else>
              <button
                :class="[
                  'nav-link w-full justify-between',
                  isActiveGroup(item.children) ? 'nav-link-active' : 'nav-link-inactive'
                ]"
                @click="toggleDropdown(item.name)"
              >
                <div class="flex items-center">
                  <Icon :name="item.icon" class="h-5 w-5 mr-3" />
                  {{ item.name }}
                </div>
                <Icon
                  :name="openDropdowns.includes(item.name) ? 'heroicons:chevron-up' : 'heroicons:chevron-down'"
                  class="h-4 w-4"
                />
              </button>
              
              <div
                v-show="openDropdowns.includes(item.name)"
                class="ml-8 mt-1 space-y-1"
              >
                <NuxtLink
                  v-for="child in item.children"
                  :key="child.name"
                  :to="child.href"
                  :class="[
                    'nav-link text-sm',
                    isActive(child.href) ? 'nav-link-active' : 'nav-link-inactive'
                  ]"
                >
                  {{ child.name }}
                  <span v-if="child.badge" class="ml-auto status-badge status-error">
                    {{ child.badge }}
                  </span>
                </NuxtLink>
              </div>
            </div>
          </template>
        </nav>

        <!-- User menu -->
        <div class="flex-shrink-0 border-t border-gray-200 dark:border-gray-700 p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="h-8 w-8 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center">
                <span class="text-sm font-medium text-primary-600 dark:text-primary-400">
                  {{ authStore.userInitials }}
                </span>
              </div>
            </div>
            <div class="ml-3 min-w-0 flex-1">
              <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                {{ authStore.userFullName }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                {{ authStore.user?.email }}
              </p>
            </div>
            <div class="ml-3">
              <Menu as="div" class="relative">
                <MenuButton class="flex items-center text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                  <Icon name="heroicons:ellipsis-vertical" class="h-5 w-5" />
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
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'

// Props
interface Props {
  isOpen: boolean
}

defineProps<Props>()

// Emits
defineEmits<{
  close: []
}>()

// Stores
const authStore = useAuthStore()
const tenantStore = useTenantStore()

// State
const openDropdowns = ref<string[]>([])

// Computed
const tenantName = computed(() => tenantStore.tenantName)

const navigation = computed(() => [
  {
    name: 'Dashboard',
    href: '/',
    icon: 'heroicons:home'
  },
  {
    name: 'Loans',
    href: '/loans',
    icon: 'heroicons:document-text',
    badge: loansStore.loanStats.underwriting || null
  },
  {
    name: 'Borrowers',
    href: '/borrowers',
    icon: 'heroicons:users'
  },
  {
    name: 'Compliance',
    href: '/compliance',
    icon: 'heroicons:shield-check',
    badge: complianceStore.violationsCount || null
  },
  {
    name: 'Workflows',
    href: '/workflows',
    icon: 'heroicons:cog-6-tooth'
  },
  {
    name: 'Documents',
    href: '/documents',
    icon: 'heroicons:folder'
  },
  {
    name: 'Integrations',
    href: '/integrations',
    icon: 'heroicons:link'
  },
  {
    name: 'Reports',
    href: '/reports',
    icon: 'heroicons:chart-bar'
  },
  {
    name: 'Settings',
    icon: 'heroicons:cog',
    children: [
      { name: 'General', href: '/settings/general' },
      { name: 'Users', href: '/settings/users' },
      { name: 'Integrations', href: '/settings/integrations' },
      { name: 'Compliance', href: '/settings/compliance' }
    ]
  }
])

// Methods
const isActive = (href: string) => {
  const route = useRoute()
  return route.path === href
}

const isActiveGroup = (children: any[]) => {
  const route = useRoute()
  return children.some(child => route.path === child.href)
}

const toggleDropdown = (name: string) => {
  const index = openDropdowns.value.indexOf(name)
  if (index > -1) {
    openDropdowns.value.splice(index, 1)
  } else {
    openDropdowns.value.push(name)
  }
}

const handleLogout = async () => {
  await authStore.logout()
}

// Initialize
onMounted(() => {
  // Load initial data for badges
  loansStore.fetchLoans(1, 10)
})
</script>
