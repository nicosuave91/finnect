<template>
  <div class="min-h-screen flex">
    <!-- Left side - Branding -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-primary-600 to-primary-800 relative overflow-hidden">
      <div class="absolute inset-0 bg-black opacity-20"></div>
      <div class="relative z-10 flex flex-col justify-center px-12 text-white">
        <div class="mb-8">
          <h1 class="text-4xl font-bold mb-4">{{ tenantName }}</h1>
          <p class="text-xl text-primary-100">{{ branding.tagline }}</p>
        </div>
        
        <div class="space-y-6">
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
              <Icon name="heroicons:shield-check" class="w-5 h-5" />
            </div>
            <div>
              <h3 class="font-semibold">Regulatory Compliance</h3>
              <p class="text-primary-100 text-sm">TRID, ECOA, RESPA, GLBA, FCRA, AML/BSA, SAFE Act</p>
            </div>
          </div>
          
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
              <Icon name="heroicons:chart-bar" class="w-5 h-5" />
            </div>
            <div>
              <h3 class="font-semibold">Workflow Automation</h3>
              <p class="text-primary-100 text-sm">Streamlined loan processing and approval workflows</p>
            </div>
          </div>
          
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
              <Icon name="heroicons:link" class="w-5 h-5" />
            </div>
            <div>
              <h3 class="font-semibold">Vendor Integrations</h3>
              <p class="text-primary-100 text-sm">Connect with 50+ mortgage industry vendors</p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Background pattern -->
      <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.1"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')"></div>
      </div>
    </div>
    
    <!-- Right side - Auth form -->
    <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-20 xl:px-24">
      <div class="mx-auto w-full max-w-sm lg:w-96">
        <!-- Logo -->
        <div class="text-center mb-8">
          <div class="mx-auto h-12 w-12 bg-primary-600 rounded-lg flex items-center justify-center">
            <Icon name="heroicons:home-modern" class="h-8 w-8 text-white" />
          </div>
          <h2 class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">
            {{ tenantName }}
          </h2>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            {{ branding.tagline }}
          </p>
        </div>
        
        <!-- Auth form -->
        <div class="card">
          <div class="card-body">
            <slot />
          </div>
        </div>
        
        <!-- Footer -->
        <div class="mt-8 text-center">
          <p class="text-xs text-gray-500 dark:text-gray-400">
            © {{ new Date().getFullYear() }} {{ branding.company_name }}. All rights reserved.
          </p>
          <div class="mt-2 flex justify-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
            <a href="#" class="hover:text-gray-700 dark:hover:text-gray-300">Privacy Policy</a>
            <a href="#" class="hover:text-gray-700 dark:hover:text-gray-300">Terms of Service</a>
            <a href="#" class="hover:text-gray-700 dark:hover:text-gray-300">Support</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Get tenant branding
const tenantStore = useTenantStore()
const tenantName = computed(() => tenantStore.tenantName)
const branding = computed(() => tenantStore.getBrandingConfiguration())
</script>