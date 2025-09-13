<template>
  <div>
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        Sign in to your account
      </h2>
      <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
        Enter your credentials to access the platform
      </p>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Email field -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Email address
        </label>
        <div class="mt-1">
          <input
            id="email"
            v-model="form.email"
            type="email"
            autocomplete="email"
            required
            class="form-input"
            :class="{ 'border-error-500': errors.email }"
            placeholder="Enter your email"
          />
        </div>
        <p v-if="errors.email" class="mt-1 text-sm text-error-600">
          {{ errors.email }}
        </p>
      </div>

      <!-- Password field -->
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Password
        </label>
        <div class="mt-1 relative">
          <input
            id="password"
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="current-password"
            required
            class="form-input pr-10"
            :class="{ 'border-error-500': errors.password }"
            placeholder="Enter your password"
          />
          <button
            type="button"
            class="absolute inset-y-0 right-0 pr-3 flex items-center"
            @click="showPassword = !showPassword"
          >
            <Icon
              :name="showPassword ? 'heroicons:eye-slash' : 'heroicons:eye'"
              class="h-5 w-5 text-gray-400 hover:text-gray-500"
            />
          </button>
        </div>
        <p v-if="errors.password" class="mt-1 text-sm text-error-600">
          {{ errors.password }}
        </p>
      </div>

      <!-- Remember me and forgot password -->
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <input
            id="remember"
            v-model="form.remember"
            type="checkbox"
            class="form-checkbox"
          />
          <label for="remember" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
            Remember me
          </label>
        </div>

        <div class="text-sm">
          <NuxtLink
            to="/auth/forgot-password"
            class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400"
          >
            Forgot your password?
          </NuxtLink>
        </div>
      </div>

      <!-- Error message -->
      <div v-if="authStore.error" class="rounded-md bg-error-50 p-4 dark:bg-error-900">
        <div class="flex">
          <Icon name="heroicons:x-circle" class="h-5 w-5 text-error-400" />
          <div class="ml-3">
            <h3 class="text-sm font-medium text-error-800 dark:text-error-200">
              Authentication Error
            </h3>
            <div class="mt-2 text-sm text-error-700 dark:text-error-300">
              {{ authStore.error }}
            </div>
          </div>
        </div>
      </div>

      <!-- Submit button -->
      <div>
        <button
          type="submit"
          :disabled="authStore.isLoading"
          class="btn-primary w-full flex justify-center"
        >
          <Icon
            v-if="authStore.isLoading"
            name="heroicons:arrow-path"
            class="animate-spin -ml-1 mr-3 h-5 w-5"
          />
          {{ authStore.isLoading ? 'Signing in...' : 'Sign in' }}
        </button>
      </div>
    </form>

    <!-- Sign up link -->
    <div class="mt-6 text-center">
      <p class="text-sm text-gray-600 dark:text-gray-400">
        Don't have an account?
        <NuxtLink
          to="/auth/register"
          class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400"
        >
          Sign up here
        </NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
// Page meta
definePageMeta({
  layout: 'auth',
  middleware: 'guest'
})

// Stores
const authStore = useAuthStore()
const router = useRouter()

// Form state
const form = reactive({
  email: '',
  password: '',
  remember: false
})

const errors = ref<Record<string, string>>({})
const showPassword = ref(false)

// Validation
const validateForm = () => {
  errors.value = {}

  if (!form.email) {
    errors.value.email = 'Email is required'
  } else if (!/\S+@\S+\.\S+/.test(form.email)) {
    errors.value.email = 'Email is invalid'
  }

  if (!form.password) {
    errors.value.password = 'Password is required'
  } else if (form.password.length < 6) {
    errors.value.password = 'Password must be at least 6 characters'
  }

  return Object.keys(errors.value).length === 0
}

// Handle form submission
const handleSubmit = async () => {
  if (!validateForm()) return

  const result = await authStore.login({
    email: form.email,
    password: form.password,
    remember: form.remember
  })

  if (result.success) {
    // Redirect to dashboard
    await router.push('/')
  }
}

// Clear errors when form changes
watch(form, () => {
  if (authStore.error) {
    authStore.clearError()
  }
  errors.value = {}
})

// Redirect if already authenticated
onMounted(() => {
  if (authStore.isAuthenticated) {
    router.push('/')
  }
})
</script>