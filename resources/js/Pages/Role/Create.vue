<script setup lang="ts">
import { reactive, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useApi } from '@/composables/useApi'
import AppSidebar from '@/Components/AppSidebar.vue'
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/Components/ui/breadcrumb'
import { Separator } from '@/Components/ui/separator'
import {
  SidebarInset,
  SidebarProvider,
  SidebarTrigger,
} from '@/Components/ui/sidebar'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import UserDropdown from '@/Components/UserDropdown.vue'
import { useI18n } from '@/plugins/i18n'

const form = reactive({
  name: '',
  description: '',
  status: 'Active'
})

const errors = reactive({})
const loading = ref(false)
const api = useApi()
const { t } = useI18n()

const saveRole = async () => {
  Object.keys(errors).forEach(key => delete errors[key])
  
  if (!form.name) errors.name = t('role_name_required')
  
  if (Object.keys(errors).length > 0) return
  
  loading.value = true
  try {
    await api.post('/api/roles', form)
    router.visit('/role')
  } catch (error) {
    console.error('Error saving role:', error)
    if (error.errors) {
      Object.assign(errors, error.errors)
    } else {
      errors.general = t('error_creating_role')
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <SidebarProvider>
    <AppSidebar />
    <SidebarInset>
      <header class="flex h-16 shrink-0 items-center gap-2 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12">
        <div class="flex items-center gap-2 px-4 flex-1">
          <SidebarTrigger class="-ml-1" />
          <Separator orientation="vertical" class="mr-2 h-4" />
          <Breadcrumb>
            <BreadcrumbList>
              <BreadcrumbItem class="hidden md:block">
                <BreadcrumbLink href="/dashboard">Dashboard</BreadcrumbLink>
              </BreadcrumbItem>
              <BreadcrumbSeparator class="hidden md:block" />
              <BreadcrumbItem>
                <BreadcrumbLink href="/role">{{ t('role_management') }}</BreadcrumbLink>
              </BreadcrumbItem>
              <BreadcrumbSeparator class="hidden md:block" />
              <BreadcrumbItem>
                <BreadcrumbPage>{{ t('add_role') }}</BreadcrumbPage>
              </BreadcrumbItem>
            </BreadcrumbList>
          </Breadcrumb>
        </div>
        <div class="px-4">
          <UserDropdown />
        </div>
      </header>
      
      <div class="flex flex-1 flex-col gap-4 p-4 pt-0">
        <div class="space-y-6">
          <h1 class="text-3xl font-bold text-gray-900">{{ t('add_role') }}</h1>
          
          <div class="bg-white rounded-lg shadow p-6">
            <div v-if="errors.general" class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
              {{ errors.general }}
            </div>
            
            <div class="space-y-4">
              <div>
                <InputLabel for="name" :value="t('role_name')" />
                <Input 
                  id="name" 
                  v-model="form.name" 
                  type="text" 
                  class="mt-1 block w-full" 
                  :placeholder="t('role_name')"
                />
                <InputError :message="errors.name" class="mt-2" />
              </div>

              <div>
                <InputLabel for="description" :value="t('description')" />
                <textarea 
                  id="description" 
                  v-model="form.description" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                  rows="3"
                  :placeholder="t('description')"
                ></textarea>
                <InputError :message="errors.description" class="mt-2" />
              </div>

              <div>
                <InputLabel for="status" :value="t('status')" />
                <select 
                  id="status" 
                  v-model="form.status" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>

              <div class="flex gap-3 pt-4">
                <Button @click="saveRole" :disabled="loading" class="bg-blue-600 hover:bg-blue-700">
                  <span v-if="loading" class="flex items-center">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                    {{ t('processing') }}
                  </span>
                  <span v-else>{{ t('create_role') }}</span>
                </Button>
                <Button @click="router.visit('/role')" variant="outline">
                  {{ t('cancel') }}
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </SidebarInset>
  </SidebarProvider>
</template>